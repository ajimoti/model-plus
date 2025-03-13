<div x-data="relationHoverCard()" 
     @mouseenter="showCard($event, '{{ class_basename($relatedRecord) }}', {{ $relatedRecord->id }})" 
     @mouseleave="hideCard"
     class="relative inline-block">
    
    <a href="#" 
       @click.prevent="loadModel('{{ Str::plural(Str::snake(class_basename(get_class($relatedRecord)))) }}', null)"
       class="text-indigo-600 hover:text-indigo-900">
        {{ $relatedRecord->{$displayColumn} }}
        <span class="text-gray-400 text-xs">(#{{ $value }})</span>
    </a>

    <!-- Hover Card -->
    <div x-show="isVisible && currentId === {{ $relatedRecord->id }}"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-1"
         @mouseleave="startHideTimer"
         @mouseenter="cancelHideTimer"
         class="absolute z-50 w-96 bg-white rounded-lg shadow-lg border border-gray-200"
         :class="position === 'top' ? 'bottom-full mb-2' : 'top-full mt-2'"
         style="display: none;">
        
        <div class="p-4">
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="text-sm font-medium text-gray-900">
                        {{ class_basename($relatedRecord) }} Details
                    </h3>
                    <div class="mt-1 space-y-1">
                        @foreach($relatedRecord->getAttributes() as $attrName => $attrValue)
                            @if(!in_array($attrName, ['id', 'password', 'remember_token', 'created_at', 'updated_at', 'deleted_at']))
                                <div class="flex text-sm">
                                    <span class="font-medium text-gray-500 w-1/3">
                                        {{ Str::title(str_replace('_', ' ', $attrName)) }}:
                                    </span>
                                    <span class="text-gray-900 w-2/3">
                                        {{ $attrValue }}
                                    </span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
            
            <div class="mt-4 flex justify-end space-x-2">
                <button type="button"
                        @click="viewDetails('{{ class_basename($relatedRecord) }}', {{ $relatedRecord->id }})"
                        class="text-sm text-indigo-600 hover:text-indigo-900">
                    View Details →
                </button>
            </div>
        </div>
    </div>
</div> 