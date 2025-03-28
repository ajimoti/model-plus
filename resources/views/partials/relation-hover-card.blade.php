<div x-data="relationHoverCard()" 
     x-init="console.log('Hover card initialized')"
     class="relative inline-block">
    
    <a href="#" 
       x-on:mouseenter="showCard($event, '{{ class_basename($relatedRecord) }}', {{ $relatedRecord->id }})"
       x-on:mouseleave="startHideTimer"
       @click.prevent="loadModel('{{ Str::plural(Str::snake(class_basename(get_class($relatedRecord)))) }}', null)"
       class="text-indigo-600 hover:text-indigo-900 relative">
        {{ $relatedRecord->{$displayColumn} }}
        <span class="text-gray-400 text-xs">(#{{ $value }})</span>
    </a>

    <!-- Hover Card -->
    <template x-teleport="body">
        <div x-show="isVisible && currentId === {{ $relatedRecord->id }}"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-1"
             @mouseenter="cancelHideTimer"
             @mouseleave="startHideTimer"
             :style="cardPosition"
             class="fixed z-[9999] w-96 bg-white rounded-lg shadow-xl border border-gray-200 max-h-[80vh] flex flex-col hover-card hover-card-scroll"
             style="display: none;">
            
            <div class="p-4 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-medium text-gray-900">
                        {{ class_basename($relatedRecord) }} Details
                    </h3>
                    <span class="text-xs text-gray-500">ID: {{ $relatedRecord->id }}</span>
                </div>
            </div>

            <div class="overflow-y-auto overscroll-contain p-4 space-y-3 flex-1 min-h-0">
                @foreach($relatedRecord->getAttributes() as $attrName => $attrValue)
                    @if(!in_array($attrName, ['id', 'password', 'remember_token', 'created_at', 'updated_at', 'deleted_at']))
                        <div class="flex text-sm items-start">
                            <span class="font-medium text-gray-500 w-1/3 flex-shrink-0 font-thin">
                                {{ Str::title(str_replace('_', ' ', $attrName)) }}:
                            </span>
                            <span class="text-gray-900 w-2/3 break-words font-light">
                                @if(is_null($attrValue))
                                    <span class="text-gray-400 italic">null</span>
                                @elseif(is_bool($attrValue))
                                    <span class="px-2 py-1 text-xs rounded-full {{ $attrValue ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $attrValue ? 'True' : 'False' }}
                                    </span>
                                @else
                                    {{ Str::limit((string)$attrValue, 150) }}
                                @endif
                            </span>
                        </div>
                    @endif
                @endforeach
            </div>
            
            <div class="p-4 border-t border-gray-100 bg-gray-50 rounded-b-lg">
                <div class="flex justify-end">
                    <span class="text-sm text-gray-500">
                        Detailed view coming soon
                        <span class="ml-2 inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">
                            Coming Soon
                        </span>
                    </span>
                </div>
            </div>
        </div>
    </template>
</div> 