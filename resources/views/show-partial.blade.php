@php
    use Vendor\ModelPlus\Helpers\ViewHelpers;
    use Illuminate\Support\Str;
@endphp

<div x-data="{ 
    sortColumn: '{{ $sortColumn ?? null }}',
    sortDirection: '{{ $sortDirection ?? 'asc' }}',
    
    sortTable(column) {
        this.sortDirection = (this.sortColumn === column && this.sortDirection === 'asc') ? 'desc' : 'asc';
        this.sortColumn = column;
        
        const url = new URL(window.location.href);
        url.searchParams.set('sort', column);
        url.searchParams.set('direction', this.sortDirection);
        url.searchParams.set('partial', true);
        
        // Store the sort state in the parent's modelFilters
        const modelSlug = '{{ $modelMap[$model] ?? '' }}';
        if (window.Alpine) {
            const parentComponent = window.Alpine.$data(document.querySelector('[x-data]'));
            if (parentComponent && parentComponent.modelFilters) {
                parentComponent.modelFilters[modelSlug] = {
                    sort: column,
                    direction: this.sortDirection
                };
            }
        }
        
        fetch(url)
            .then(response => response.text())
            .then(html => {
                const temp = document.createElement('div');
                temp.innerHTML = html;
                const newTableContainer = temp.querySelector('#table-container');
                document.getElementById('table-container').innerHTML = newTableContainer.innerHTML;
                
                // Update URL without page refresh
                const newUrl = new URL(window.location.href);
                newUrl.searchParams.set('sort', column);
                newUrl.searchParams.set('direction', this.sortDirection);
                window.history.pushState({}, '', newUrl);
            })
            .catch(error => console.error('Error:', error));
    },

    editRecord(model, id) {
        // TODO: Implement edit functionality
        console.log('Edit record:', model, id);
    },

    viewRecord(model, id) {
        // TODO: Implement view functionality
        console.log('View record:', model, id);
    },

    deleteRecord(model, id) {
        if (!confirm('Are you sure you want to delete this record?')) {
            return;
        }
        
        // TODO: Implement delete functionality
        console.log('Delete record:', model, id);
    },

    hoverCardData: null,
    hoverCardTimer: null,
    
    async fetchRelationDetails(model, id) {
        const url = new URL(window.location.href);
        url.searchParams.set('action', 'fetch_relation');
        url.searchParams.set('model', model);
        url.searchParams.set('id', id);
        
        try {
            const response = await fetch(url);
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Error fetching relation details:', error);
            return null;
        }
    }
}">

<div class="sm:flex sm:items-center">
    <div class="sm:flex-auto">
        <h1 class="text-base font-semibold leading-6 text-gray-900">
            {{ $modelName }}
        </h1>
        <p class="mt-2 text-sm text-gray-700">
            A list of all {{ Str::lower($modelName) }} records in your database.
        </p>
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
    <div id="table-container" class="mt-8 flow-root">
        @if($records->isEmpty())
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <h3 class="mt-2 text-sm font-semibold text-gray-900">No records found</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by creating a new {{ strtolower($modelName) }}.</p>
            </div>
        @else
            <div class="table-container">
                <div class="table-scroll-container" x-data="tableScroll" @scroll="handleScroll">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                @foreach($records->first()?->getAttributes() ?? [] as $column => $value)
                                    <th scope="col" 
                                        @class([
                                            'py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6 max-w-xs',
                                            'sticky-col' => $loop->first
                                        ])>
                                        <button type="button" 
                                                @click.prevent="sortTable('{{ $column }}')"
                                                class="group inline-flex items-center">
                                            <span class="truncate">{{ Str::title(str_replace('_', ' ', $column)) }}</span>
                                            @if(isset($relationships['foreign_keys'][$column]))
                                                @php
                                                    $relationMethod = $relationships['foreign_keys'][$column];
                                                    $relationType = $relationships['methods'][$relationMethod]['type'] ?? '';
                                                @endphp
                                                <span class="ml-1 text-xs text-gray-500">({{ $relationType }})</span>
                                            @endif
                                            {!! ViewHelpers::getSortIcon($column, $sortColumn ?? null, $sortDirection ?? 'asc') !!}
                                        </button>
                                    </th>
                                @endforeach
                                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="table-body" class="divide-y divide-gray-200 bg-white">
                            @foreach($records as $record)
                                <tr class="hover:bg-gray-50">
                                    @foreach($record->getAttributes() as $column => $value)
                                        @if(isset($relationships['foreign_keys'][$column]))
                                            @include('modelplus::partials.relationship-cell', [
                                                'column' => $column,
                                                'value' => $value,
                                                'record' => $record,
                                                'relationships' => $relationships,
                                                'loop' => $loop
                                            ])
                                        @else
                                            <td @class([
                                                'whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6 max-w-xs',
                                                'sticky-col' => $loop->first,
                                                'bg-white' => true
                                            ])>
                                                <div class="truncate" title="{{ $value }}">{{ $value }}</div>
                                            </td>
                                        @endif
                                    @endforeach
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                        @include('modelplus::partials.row-actions', ['record' => $record])
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-6" id="pagination">
                @include('modelplus::partials.pagination')
            </div>
        @endif
    </div>
@endif
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

    document.addEventListener('alpine:init', () => {
        Alpine.data('tableScroll', () => ({
            init() {
                this.handleScroll({ target: this.$el });
            },
            handleScroll(e) {
                const el = e.target;
                const isStart = el.scrollLeft > 0;
                const isEnd = el.scrollLeft + el.clientWidth < el.scrollWidth;
                
                el.classList.toggle('shadow-start', isStart);
                el.classList.toggle('shadow-end', isEnd);
            }
        }));

        Alpine.data('relationHoverCard', () => ({
            isVisible: false,
            currentId: null,
            hideTimer: null,
            showTimer: null,
            cardPosition: {},
            
            showCard(event, model, id) {
                this.cancelHideTimer();
                this.cancelShowTimer();
                
                this.showTimer = setTimeout(() => {
                    const rect = event.target.closest('a').getBoundingClientRect();
                    const cardWidth = 384;
                    const cardHeight = Math.min(window.innerHeight * 0.8, 400);
                    
                    const navElement = document.querySelector('.sticky.top-0');
                    const navHeight = navElement ? navElement.offsetHeight : 0;
                    
                    const viewportWidth = window.innerWidth;
                    const viewportHeight = window.innerHeight;
                    
                    // Get table scroll container
                    const tableContainer = event.target.closest('.table-scroll-container');
                    const tableRect = tableContainer.getBoundingClientRect();
                    
                    // Calculate available space in different directions
                    const spaceAbove = rect.top - Math.max(navHeight, tableRect.top);
                    const spaceBelow = Math.min(viewportHeight, tableRect.bottom) - rect.bottom;
                    const spaceRight = viewportWidth - rect.right;
                    const spaceLeft = rect.left;
                    
                    // Determine horizontal position
                    let left;
                    if (spaceRight >= cardWidth + 8) {
                        // Prefer right if there's enough space
                        left = rect.right + 8;
                    } else if (spaceLeft >= cardWidth + 8) {
                        // Try left if there's enough space
                        left = rect.left - cardWidth - 8;
                    } else {
                        // Align with the link's left edge, but ensure it stays within viewport
                        left = Math.max(8, Math.min(viewportWidth - cardWidth - 8, rect.left));
                    }
                    
                    // Determine vertical position
                    let top;
                    if (spaceBelow >= cardHeight + 8) {
                        // Prefer below the link if there's space
                        top = rect.bottom + 8;
                    } else if (spaceAbove >= cardHeight + 8) {
                        // Try above if there's space
                        top = rect.top - cardHeight - 8;
                    } else {
                        // Position relative to the table container
                        if (rect.top < tableRect.top + (tableRect.height / 2)) {
                            // Link is in upper half of table - align with top of table
                            top = tableRect.top + 8;
                        } else {
                            // Link is in lower half - align with bottom of viewport or table
                            top = Math.min(tableRect.bottom, viewportHeight) - cardHeight - 8;
                        }
                    }
                    
                    this.cardPosition = {
                        position: 'fixed',
                        top: `${top}px`,
                        left: `${left}px`,
                        maxHeight: `${cardHeight}px`,
                        width: `${cardWidth}px`
                    };
                    
                    this.currentId = id;
                    this.isVisible = true;
                }, 250);
            },
            
            startHideTimer() {
                if (this.hideTimer) return;
                
                this.hideTimer = setTimeout(() => {
                    this.hideCard();
                }, 300);
            },
            
            cancelShowTimer() {
                if (this.showTimer) {
                    clearTimeout(this.showTimer);
                    this.showTimer = null;
                }
            },
            
            cancelHideTimer() {
                if (this.hideTimer) {
                    clearTimeout(this.hideTimer);
                    this.hideTimer = null;
                }
            },
            
            hideCard() {
                this.cancelShowTimer();
                this.isVisible = false;
                this.currentId = null;
                this.hideTimer = null;
            },
            
            viewDetails(model, id) {
                this.hideCard();
                this.$dispatch('view-record', { model, id });
            }
        }));
    });
</script> 