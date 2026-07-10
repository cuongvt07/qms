<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class AuditLog extends Component
{
    use WithPagination;

    public string $userId  = '';
    public string $action  = '';
    public string $date    = '';
    public string $session = '';
    public string $q       = '';

    public function updating(): void
    {
        $this->resetPage();
    }

    public function filterSession(string $sid): void
    {
        $this->session = $sid;
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['userId', 'action', 'date', 'session', 'q']);
        $this->resetPage();
    }

    public function render()
    {
        $logs = ActivityLog::with('user')
            ->when($this->userId !== '', fn ($x) => $x->where('user_id', $this->userId))
            ->when($this->action !== '', fn ($x) => $x->where('action', $this->action))
            ->when($this->session !== '', fn ($x) => $x->where('session_id', $this->session))
            ->when($this->date !== '', fn ($x) => $x->whereDate('created_at', $this->date))
            ->when($this->q !== '', fn ($x) => $x->where('description', 'like', '%' . $this->q . '%'))
            ->latest('id')
            ->paginate(40);

        return view('livewire.admin.audit-log', [
            'logs'    => $logs,
            'users'   => User::orderBy('name')->get(['id', 'name']),
            'actions' => self::ACTIONS,
        ]);
    }

    public const ACTIONS = [
        'login'             => 'Đăng nhập',
        'logout'            => 'Đăng xuất',
        'save'              => 'Lưu dữ liệu',
        'copy'              => 'Sao chép ngày',
        'upload'            => 'Đính kèm tệp',
        'delete_attachment' => 'Xoá đính kèm',
        'export'            => 'Tải .docx',
    ];
}
