<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ApplicationSetting;
use App\Models\User;
use App\Services\SystemBackupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class SystemBackupController extends Controller
{
    public function store(
        Request $request,
        SystemBackupService $backupService
    ): RedirectResponse {
        abort_unless(
            $request->user()?->isAdmin(),
            403
        );

        try {
            $backup = $backupService->create(
                SystemBackupService::TYPE_MANUAL,
                (int) $request->user()->id,
                $request->ip()
            );

            ActivityLog::record(
                'system.backup_created',
                null,
                [
                    'filename' => $backup['filename'],
                    'type' => SystemBackupService::TYPE_MANUAL,
                    'size' => $backup['size'],
                    'sha256' => $backup['sha256'],
                ]
            );

            return redirect()
                ->to(
                    route('settings.index')
                    .'#system-backups'
                )
                ->with(
                    'success',
                    'Datenbank-Backup wurde erfolgreich erstellt.'
                );
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->to(
                    route('settings.index')
                    .'#system-backups'
                )
                ->with(
                    'error',
                    'Backup konnte nicht erstellt werden. Bitte Serverprotokoll prüfen.'
                );
        }
    }

    public function showRestore(
        Request $request,
        string $filename,
        SystemBackupService $backupService
    ): View {
        abort_unless(
            $request->user()?->isAdmin(),
            403
        );

        try {
            $backup = $backupService->findBackup(
                $filename
            );
        } catch (RuntimeException) {
            abort(404);
        }

        return view(
            'pages.settings.backup-restore',
            [
                'backup' => $backup,
            ]
        );
    }

    public function restore(
        Request $request,
        string $filename,
        SystemBackupService $backupService
    ): RedirectResponse {
        abort_unless(
            $request->user()?->isAdmin(),
            403
        );

        $request->validate(
            [
                'current_password' => [
                    'required',
                    'current_password',
                ],
                'confirmation' => [
                    'required',
                    Rule::in([
                        'BACKUP WIEDERHERSTELLEN',
                    ]),
                ],
                'acknowledge' => [
                    'required',
                    'accepted',
                ],
            ],
            [
                'confirmation.in' => 'Bitte exakt BACKUP WIEDERHERSTELLEN eingeben.',
            ]
        );

        $admin = $request->user();

        $adminId = (int) $admin->id;
        $adminEmail = (string) $admin->email;

        try {
            $result = $backupService->restore(
                $filename,
                $adminId,
                $request->ip()
            );
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->back()
                ->withInput(
                    $request->except(
                        'current_password'
                    )
                )
                ->with(
                    'error',
                    'Die Wiederherstellung konnte nicht abgeschlossen werden. Bitte Serverprotokoll prüfen.'
                );
        }

        /*
         * Ab hier ist der Restore erfolgreich.
         * Die wiederhergestellte Session darf unter keinen
         * Umständen am Request-Ende erneut gespeichert werden.
         */
        Auth::guard()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        /*
         * Ein Audit-Fehler darf einen bereits erfolgreichen
         * Restore weder als fehlgeschlagen melden noch die
         * alte Admin-Session wiederherstellen.
         */
        try {
            $auditUserId = User::query()
                ->whereKey($adminId)
                ->exists()
                    ? $adminId
                    : null;

            ActivityLog::query()->create([
                'user_id' => $auditUserId,
                'action' => 'system.backup_restored',
                'entity' => null,
                'entity_id' => null,
                'ip_address' => $request->ip(),
                'properties' => [
                    'restored_filename' => $result['restored']['filename'],
                    'restored_sha256' => $result['restored']['sha256'],
                    'safety_backup_filename' => $result['safety_backup']['filename'],
                    'safety_backup_sha256' => $result['safety_backup']['sha256'],
                    'requested_by_user_id' => $adminId,
                    'requested_by_email' => $adminEmail,
                ],
            ]);
        } catch (Throwable $auditException) {
            report($auditException);
        }

        return redirect()
            ->route('login');
    }

    public function download(
        Request $request,
        string $filename,
        SystemBackupService $backupService
    ): BinaryFileResponse {
        abort_unless(
            $request->user()?->isAdmin(),
            403
        );

        try {
            $path = $backupService->backupPath(
                $filename
            );
        } catch (RuntimeException) {
            abort(404);
        }

        return response()->download(
            $path,
            basename($filename),
            [
                'Content-Type' => 'application/gzip',
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, no-store, max-age=0',
            ]
        );
    }

    public function destroy(
        Request $request,
        string $filename,
        SystemBackupService $backupService
    ): RedirectResponse {
        abort_unless(
            $request->user()?->isAdmin(),
            403
        );

        try {
            $backupService->delete(
                $filename
            );

            ActivityLog::record(
                'system.backup_deleted',
                null,
                [
                    'filename' => basename($filename),
                ]
            );

            return redirect()
                ->to(
                    route('settings.index')
                    .'#system-backups'
                )
                ->with(
                    'success',
                    'Backup wurde gelöscht.'
                );
        } catch (RuntimeException $exception) {
            report($exception);

            return redirect()
                ->to(
                    route('settings.index')
                    .'#system-backups'
                )
                ->with(
                    'error',
                    'Backup konnte nicht gelöscht werden. Bitte Serverprotokoll prüfen.'
                );
        }
    }

    public function updateSettings(
        Request $request
    ): RedirectResponse {
        abort_unless(
            $request->user()?->isAdmin(),
            403
        );

        $data = $request->validate([
            'backup_automatic_enabled' => [
                'nullable',
                'boolean',
            ],
            'backup_automatic_interval' => [
                'required',
                Rule::in([
                    '6_hours',
                    '12_hours',
                    'daily',
                    'weekly',
                ]),
            ],
            'backup_automatic_retention' => [
                'required',
                'integer',
                'min:1',
                'max:90',
            ],
        ]);

        $enabled = $request->boolean(
            'backup_automatic_enabled'
        );

        ApplicationSetting::putValue(
            'backup_automatic_enabled',
            $enabled ? '1' : '0',
            'boolean',
            'Automatische Datenbank-Backups aktivieren'
        );

        ApplicationSetting::putValue(
            'backup_automatic_interval',
            $data['backup_automatic_interval'],
            'string',
            'Intervall der automatischen Datenbank-Backups'
        );

        ApplicationSetting::putValue(
            'backup_automatic_retention',
            $data['backup_automatic_retention'],
            'integer',
            'Anzahl aufzubewahrender automatischer Datenbank-Backups'
        );

        ActivityLog::record(
            'system.backup_settings_updated',
            null,
            [
                'enabled' => $enabled,
                'interval' => $data['backup_automatic_interval'],
                'retention' => (int) $data[
                        'backup_automatic_retention'
                    ],
            ]
        );

        return redirect()
            ->to(
                route('settings.index')
                .'#system-backups'
            )
            ->with(
                'success',
                'Backup-Einstellungen wurden gespeichert.'
            );
    }
}
