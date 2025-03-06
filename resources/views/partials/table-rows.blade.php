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
                        <a href="#" 
                           @click.prevent="loadModel('{{ Str::plural(Str::snake(class_basename(get_class($relatedRecord)))) }}', null)"
                           class="text-indigo-600 hover:text-indigo-900">
                            {{ $relatedRecord->{$displayColumn} }}
                            <span class="text-gray-400 text-xs">(#{{ $value }})</span>
                        </a>
                    @else
                        <span class="text-gray-400">{{ $value ?? 'N/A' }}</span>
                    @endif
                @else
                    {{ $value }}
                @endif
            </td>
        @endforeach
        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
            <div class="flex justify-end gap-2">
                <a href="#" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                <span class="text-gray-300">|</span>
                <a href="#" class="text-indigo-600 hover:text-indigo-900">View</a>
            </div>
        </td>
    </tr>
@endforeach 