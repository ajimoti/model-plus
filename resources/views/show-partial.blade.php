<div class="sm:flex sm:items-center">
    <div class="sm:flex-auto">
        <h1 class="text-base font-semibold leading-6 text-gray-900">{{ $modelName }}</h1>
        <p class="mt-2 text-sm text-gray-700">A list of all {{ strtolower($modelName) }} records in your database.</p>
    </div>
    <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
        <button type="button" class="block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            Add {{ $modelName }}
        </button>
    </div>
</div>

<div id="table-container">
    <div class="mt-8 flow-root">
        @if($records->isEmpty())
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <h3 class="mt-2 text-sm font-semibold text-gray-900">No records found</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by creating a new {{ strtolower($modelName) }}.</p>
            </div>
        @else
            <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                                <tr>
                                    @foreach($records->first()?->getAttributes() ?? [] as $column => $value)
                                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">
                                            {{ Str::title(str_replace('_', ' ', $column)) }}
                                            @if(isset($relationships['foreign_keys'][$column]))
                                                @php
                                                    $relationMethod = $relationships['foreign_keys'][$column];
                                                    $relationType = $relationships['methods'][$relationMethod]['type'];
                                                @endphp
                                                <span class="ml-1 text-xs text-gray-500">({{ $relationType }})</span>
                                            @endif
                                        </th>
                                    @endforeach
                                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                        <span class="sr-only">Actions</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @foreach($records as $record)
                                    <tr>
                                        @foreach($record->getAttributes() as $column => $value)
                                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                                @if(isset($relationships['foreign_keys'][$column]))
                                                    @php
                                                        $relationMethod = $relationships['foreign_keys'][$column];
                                                        $relatedRecord = $record->{$relationMethod};
                                                        $displayColumn = $relatedRecord ? 
                                                            collect($relatedRecord->getAttributes())
                                                                ->filter(fn($val, $key) => 
                                                                    in_array(gettype($val), ['string']) && 
                                                                    !in_array($key, ['password', 'remember_token'])
                                                                )
                                                                ->keys()
                                                                ->first() : null;
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
                                                <a href="#" class="text-indigo-600 hover:text-indigo-900">
                                                    Edit
                                                </a>
                                                <span class="text-gray-300">|</span>
                                                <a href="#" class="text-indigo-600 hover:text-indigo-900">
                                                    View
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="mt-6">
        @foreach ($records->links()->elements as $element)
            @if (is_string($element))
                <span class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 focus:outline-offset-0">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    <button 
                        @click.prevent="loadModel('{{ $modelMap[$model] ?? '' }}', 'page={{ $page }}')"
                        class="relative inline-flex items-center px-4 py-2 text-sm font-semibold ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0"
                        :class="{'bg-indigo-600 text-white': window.location.search.includes('page={{ $page }}')}">
                        {{ $page }}
                    </button>
                @endforeach
            @endif
        @endforeach
    </div>
</div>

<script>
    document.querySelectorAll('.pagination a').forEach(link => {
        link.addEventListener('click', async (e) => {
            e.preventDefault();
            const url = new URL(e.target.href, window.location.origin);
            
            try {
                const response = await fetch(`${url}&partial=true`);
                const html = await response.text();
                
                // Create a temporary container
                const temp = document.createElement('div');
                temp.innerHTML = html;
                
                // Find the table container in the response
                const newTableContainer = temp.querySelector('#table-container');
                
                // Replace the current table container with the new one
                document.getElementById('table-container').innerHTML = newTableContainer.innerHTML;
                
                // Update URL without page refresh
                window.history.pushState({}, '', url);
            } catch (error) {
                console.error('Error loading data:', error);
            }
        });
    });
</script> 