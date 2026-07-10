<x-layouts.app>
    <x-slot name="title">Mục tài liệu</x-slot>

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Mục tài liệu (TL)</h1>
        <a href="{{ route('admin.document-categories.create') }}"
           class="px-4 py-2 bg-teal-600 text-white rounded-md text-sm font-medium hover:bg-teal-700">
            + Thêm mục tài liệu
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tên mục</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mô tả</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Số BM</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Trạng thái</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($categories as $category)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $category->ten_muc }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $category->mo_ta ?: '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $category->form_templates_count }}</td>
                        <td class="px-4 py-3">
                            @if($category->is_active)
                                <span class="px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700">Đang dùng</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-500">Ngừng</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <a href="{{ route('admin.document-categories.edit', $category) }}"
                               class="text-xs text-gray-500 hover:text-gray-700">Chỉnh sửa</a>
                            <form method="POST" action="{{ route('admin.document-categories.destroy', $category) }}"
                                  class="inline" onsubmit="return confirm('Xóa mục tài liệu này?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-red-500 hover:text-red-700">Xóa</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-400">
                            Chưa có mục tài liệu nào.
                            <a href="{{ route('admin.document-categories.create') }}" class="text-teal-500">Thêm ngay</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.app>
