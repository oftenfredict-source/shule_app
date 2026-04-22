@if($permissions->count() === 0)
    <div class="alert alert-light text-muted border-0 py-4 text-center" style="border-radius:0;">No permission records found for this category.</div>
@else
    <div class="table-responsive">
        <table class="table table-hover align-middle border">
            <thead class="bg-light">
                <tr style="font-size: 11px; text-transform: uppercase; color: #940000; letter-spacing: 0.5px;">
                    <th class="py-3 px-3">Date Requested</th>
                    <th class="py-3 px-3">Student Name</th>
                    <th class="py-3 px-3">Period & Duration</th>
                    <th class="py-3 px-3">Category</th>
                    <th class="py-3 px-3 text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($permissions as $permission)
                    <tr>
                        <td class="px-3" style="font-size: 13px;">{{ $permission->created_at ? $permission->created_at->format('d M Y, H:i') : '---' }}</td>
                        <td class="px-3">
                            <div style="font-weight: 700; color: #333;">{{ $studentNames[$permission->studentID] ?? 'Processing...' }}</div>
                        </td>
                        <td class="px-3" style="font-size: 13px;">
                            <i class="bx bx-calendar text-muted me-1"></i> {{ \Carbon\Carbon::parse($permission->start_date)->format('d/m') }} to {{ \Carbon\Carbon::parse($permission->end_date)->format('d/m/Y') }}
                            <div class="badge bg-light text-dark border ms-2" style="border-radius:0; font-weight:700;">{{ $permission->days_count }} DAYS</div>
                        </td>
                        <td class="px-3"><span class="text-uppercase small font-weight-bold" style="color:#777;">{{ $permission->reason_type }}</span></td>
                        <td class="px-3 text-center">
                            @php
                                $status = strtolower($permission->status);
                                $badgeClass = match($status) {
                                    'approved' => 'bg-success',
                                    'rejected' => 'bg-danger',
                                    default => 'bg-warning text-dark'
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }} text-uppercase" style="border-radius: 0; font-size: 10px; padding: 5px 10px;">
                                {{ $permission->status }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
