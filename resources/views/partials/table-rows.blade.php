@foreach($records as $record)
    <tr>
        @foreach($record->getAttributes() as $column => $value)
            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                @if(isset($relationships['foreign_keys'][$column]))
                    @php
                        $relationMethod = $relationships['foreign_keys'][$column];
                        $relatedRecord = $record->{$relationMethod};
                        $displayColumn = null;
                        
                        if ($relatedRecord) {
                            $displayColumn = app(Vendor\ModelPlus\Services\ModelDiscoveryService::class)
                                ->getDisplayColumnForModel($relatedRecord);
                        }
                    @endphp
                    @if($relatedRecord && $displayColumn)
                        @include('modelplus::partials.relation-hover-card', [
                            'relatedRecord' => $relatedRecord,
                            'displayColumn' => $displayColumn,
                            'value' => $value
                        ])
                    @else
                        <span class="text-gray-400">{{ $value ?? 'N/A' }}</span>
                    @endif
                @else
                    {{ $value }}
                @endif
            </td>
        @endforeach
        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
            @include('modelplus::partials.row-actions')
        </td>
    </tr>
@endforeach 