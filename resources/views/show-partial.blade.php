@php
    function getSortIcon($column, $currentSort, $currentDirection) {
        if ($currentSort !== $column) {
            return <<<HTML
                <svg class="w-3 h-3 ml-1.5 opacity-0 group-hover:opacity-50" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
HTML;
        }
        
        if ($currentDirection === 'asc') {
            return <<<HTML
                <svg class="w-3 h-3 ml-1.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"/>
                </svg>
HTML;
        }
        
        return <<<HTML
            <svg class="w-3 h-3 ml-1.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
HTML;
    }
@endphp

<div class="sm:flex sm:items-center">
    <div class="sm:flex-auto">
        <h1 class="text-base font-semibold leading-6 text-gray-900">{{ $modelName }}</h1>
        <p class="mt-2 text-sm text-gray-700">A list of all {{ strtolower($modelName) }} records in your database.</p>
    </div>
    @if(!isset($error))
    <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
        <button type="button" class="block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            Add {{ $modelName }}
        </button>
    </div>
    @endif
</div>

@if(isset($error) && $error === 'table_not_found')
    <div class="mt-8 rounded-md bg-yellow-50 p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800">Database Table Not Found</h3>
                <div class="mt-2 text-sm text-yellow-700">
                    <p>The model <code class="px-1.5 py-0.5 rounded-md bg-yellow-100 font-mono text-sm">{{ class_basename($model) }}</code> exists, but its corresponding database table <code class="px-1.5 py-0.5 rounded-md bg-yellow-100 font-mono text-sm">{{ $table }}</code> was not found.</p>
                    <div class="mt-4 text-sm">
                        <p class="font-medium">Possible solutions:</p>
                        <ul class="mt-2 list-disc list-inside space-y-1">
                            <li>Run your database migrations: <code class="px-1.5 py-0.5 rounded-md bg-yellow-100 font-mono text-sm">php artisan migrate</code></li>
                            <li>Check if the migration file exists in <code class="px-1.5 py-0.5 rounded-md bg-yellow-100 font-mono text-sm">database/migrations</code></li>
                            <li>Verify the table name in your model matches the migration</li>
                        </ul>
                    </div>
                    <div class="mt-4 flex space-x-3">
                        <button 
                            type="button"
                            @click="loadModel('{{ $modelMap[$model] ?? '' }}')"
                            class="inline-flex items-center rounded-md bg-yellow-100 px-2.5 py-1.5 text-sm font-semibold text-yellow-800 hover:bg-yellow-200 focus:outline-none focus:ring-2 focus:ring-yellow-600 focus:ring-offset-2">
                            <svg class="mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                            </svg>
                            Retry
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@else
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
                                                <button type="button" 
                                                        @click.prevent="loadModel('{{ $modelMap[$model] ?? '' }}', 'sort={{ $column }}&direction={{ ($sortColumn === $column && $sortDirection === 'asc') ? 'desc' : 'asc' }}')"
                                                        class="group inline-flex items-center">
                                                    {{ Str::title(str_replace('_', ' ', $column)) }}
                                                    @if(isset($relationships['foreign_keys'][$column]))
                                                        @php
                                                            $relationMethod = $relationships['foreign_keys'][$column];
                                                            $relationType = $relationships['methods'][$relationMethod]['type'];
                                                        @endphp
                                                        <span class="ml-1 text-xs text-gray-500">({{ $relationType }})</span>
                                                    @endif
                                                    {!! getSortIcon($column, $sortColumn, $sortDirection) !!}
                                                </button>
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
@endif

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