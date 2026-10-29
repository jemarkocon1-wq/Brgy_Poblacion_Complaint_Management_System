<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><title>Barangay complaint report</title><style>body{font-family:DejaVu Sans,sans-serif;color:#222;font-size:10px}h1{color:#7b1e2b;font-size:20px;margin-bottom:4px}p{color:#555}.brand{border-bottom:2px solid #d4a017;padding-bottom:12px;margin-bottom:18px}table{width:100%;border-collapse:collapse}th{background:#7b1e2b;color:white;text-align:left}th,td{padding:7px;border:1px solid #ddd;vertical-align:top}tr:nth-child(even){background:#f8f6f4}</style></head>
<body><div class="brand"><h1>Barangay Poblacion</h1><p>Complaint Management Report · Generated {{ $generatedAt->format('F j, Y g:i A') }}</p></div>
<table><thead><tr><th>Reference</th><th>Title</th><th>Complainant</th><th>Respondent(s)</th><th>Witnesses</th><th>Category</th><th>Status</th><th>Assigned staff</th><th>Submitted</th></tr></thead><tbody>
@foreach ($complaints as $complaint)<tr><td>{{ $complaint->reference }}</td><td>{{ $complaint->title }}</td><td>{{ $complaint->resident?->name ?? '—' }}</td><td>{{ $complaint->respondents ?: '—' }}</td><td>{{ $complaint->witnesses ?: '—' }}</td><td>{{ str_replace('_', ' ', $complaint->category) }}</td><td>{{ $complaint->statusLabel() }}</td><td>{{ $complaint->assignedStaff?->name ?? '—' }}</td><td>{{ $complaint->created_at?->format('Y-m-d') }}</td></tr>@endforeach
</tbody></table></body></html>
