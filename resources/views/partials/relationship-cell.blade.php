@php
    $relationMethod = $relationships['foreign_keys'][$column];
    $relatedRecord = $record->{$relationMethod};
    $displayColumn = null;
    
    if ($relatedRecord) {
        $displayColumn = app(Vendor\ModelPlus\Services\ModelDiscoveryService::class)
            ->getDisplayColumnForModel($relatedRecord);
    }
@endphp

<td @class([
    'whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium sm:pl-6',
    'sticky-col' => $loop->first,
    'bg-white' => true
])>
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
</td> 