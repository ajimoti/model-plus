<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ModelPlus - {{ $title ?? 'Dashboard' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://unpkg.com/@heroicons/v2/24/outline@0.1.0/index.min.js"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        .relation-link {
            @apply text-indigo-600 hover:text-indigo-900 transition-colors duration-150;
        }
        
        .relation-badge {
            @apply inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10;
        }
        
        .table-hover tr:hover {
            @apply bg-gray-50 transition-colors duration-150;
        }
        
        .action-button {
            @apply inline-flex items-center rounded-md bg-white px-2.5 py-1.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50;
        }
        
        .table-container {
            position: relative;
            overflow: hidden;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
        }
        
        .table-scroll-container {
            overflow-x: auto;
            position: relative;
        }
        
        /* Remove the gradient shadows completely */
        .table-scroll-container::before,
        .table-scroll-container::after {
            display: none;
        }
        
        /* Fixed first column */
        .sticky-col {
            position: sticky;
            left: 0;
            z-index: 1;
            background-color: white;
            transition: box-shadow 0.2s ease, background-color 0.2s ease;
        }
        
        /* Update background color when in header */
        thead .sticky-col {
            background-color: rgb(249 250 251); /* Matches Tailwind's bg-gray-50 */
            z-index: 3;
        }
        
        /* Ensure proper background on row hover */
        tr:hover .sticky-col {
            background-color: rgb(249 250 251); /* Matches Tailwind's hover:bg-gray-50 */
        }
        
        /* Modern shadow effect on scroll */
        .table-scroll-container.shadow-start .sticky-col {
            box-shadow: 4px 0 8px -2px rgba(0, 0, 0, 0.1);
        }
        
        /* Ensure proper z-indexing for shadow effects */
        .table-scroll-container.shadow-start thead .sticky-col {
            box-shadow: 4px 0 8px -2px rgba(0, 0, 0, 0.12);
        }
        
        /* Table cell styles */
        .table-cell {
            @apply px-3 py-4 text-sm text-gray-500;
            position: relative;
        }
        
        /* Cell content handling */
        .cell-content {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .cell-content-wrap {
            white-space: normal;
            word-break: break-word;
        }
        
        /* Modern button styles */
        .table-action-btn {
            @apply inline-flex items-center px-2.5 py-1.5 text-sm font-medium text-gray-700 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200;
        }
    </style>
</head>
<body class="h-full" x-data="{ 
    sidebarOpen: false,
    currentModel: null,
    loading: false,
    content: '',
    searchQuery: '',
    modelFilters: {},
    
    async loadModel(modelSlug, queryParams = null) {
        if (this.loading) return;
        
        this.loading = true;
        let url = `{{ route('modelplus.show', ['model' => ':slug']) }}`.replace(':slug', modelSlug);
        
        // Create URL object for proper query parameter handling
        const urlObj = new URL(url, window.location.origin);
        
        // Add partial parameter
        urlObj.searchParams.append('partial', 'true');
        
        // Add search query if present
        if (this.searchQuery) {
            urlObj.searchParams.append('search', this.searchQuery);
        }
        
        // If we have stored filters for this model, use them
        if (this.modelFilters[modelSlug]) {
            const filters = this.modelFilters[modelSlug];
            if (filters.sort) {
                urlObj.searchParams.append('sort', filters.sort);
                urlObj.searchParams.append('direction', filters.direction || 'asc');
            }
        }
        
        // If new query params are provided, update the stored filters
        if (queryParams) {
            const params = new URLSearchParams(queryParams);
            if (params.has('sort')) {
                this.modelFilters[modelSlug] = {
                    sort: params.get('sort'),
                    direction: params.get('direction') || 'asc'
                };
            }
            // Add the new params to the URL
            params.forEach((value, key) => {
                urlObj.searchParams.set(key, value);
            });
        }
        
        try {
            const response = await fetch(urlObj.toString());
            if (!response.ok) throw new Error('Network response was not ok');
            
            const html = await response.text();
            this.content = html;
            this.currentModel = modelSlug;
            
            // Update URL without page refresh (exclude 'partial' from visible URL)
            urlObj.searchParams.delete('partial');
            window.history.pushState({}, '', urlObj.toString());
        } catch (error) {
            console.error('Error loading model:', error);
        } finally {
            this.loading = false;
        }
    },
    init() {
        // Handle browser back/forward buttons
        window.addEventListener('popstate', () => {
            const modelSlug = window.location.pathname.split('/').pop();
            const queryString = window.location.search.substring(1);
            if (modelSlug) this.loadModel(modelSlug, queryString);
        });
        
        // Load initial content if we're on a model page
        const initialModel = window.location.pathname.split('/').pop();
        if (initialModel && initialModel !== 'modelplus') {
            const queryString = window.location.search.substring(1);
            this.loadModel(initialModel, queryString);
        }
    },
    async sortTable(column) {
        const direction = (this.sortColumn === column && this.sortDirection === 'asc') ? 'desc' : 'asc';
        this.sortColumn = column;
        this.sortDirection = direction;
        
        // Show loading state only for table body
        const tableBody = document.getElementById('table-body');
        tableBody.style.opacity = '0.5';
        
        const url = `{{ route('modelplus.show', ['model' => ':slug']) }}`.replace(':slug', this.currentModel);
        const urlObj = new URL(url, window.location.origin);
        
        urlObj.searchParams.append('partial', 'true');
        urlObj.searchParams.append('sort', column);
        urlObj.searchParams.append('direction', direction);
        
        if (this.searchQuery) {
            urlObj.searchParams.append('search', this.searchQuery);
        }
        
        try {
            const response = await fetch(urlObj.toString());
            if (!response.ok) throw new Error('Network response was not ok');
            
            const html = await response.text();
            const temp = document.createElement('div');
            temp.innerHTML = html;
            
            // Update only the table body and pagination
            const newTableBody = temp.querySelector('#table-body');
            const newPagination = temp.querySelector('#pagination');
            
            if (newTableBody) {
                tableBody.innerHTML = newTableBody.innerHTML;
            }
            
            if (newPagination) {
                document.getElementById('pagination').innerHTML = newPagination.innerHTML;
            }
            
            // Update URL without page refresh
            urlObj.searchParams.delete('partial');
            window.history.pushState({}, '', urlObj.toString());
        } catch (error) {
            console.error('Error sorting table:', error);
        } finally {
            tableBody.style.opacity = '1';
        }
    }
}">
    @php
    use Illuminate\Support\Str;
    @endphp
    
    <div>
        <!-- Off-canvas menu for mobile -->
        <div x-show="sidebarOpen" class="relative z-50 lg:hidden" role="dialog" aria-modal="true">
            <div x-show="sidebarOpen" 
                 x-transition:enter="transition-opacity ease-linear duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-900/80"></div>

            <div class="fixed inset-0 flex">
                <div x-show="sidebarOpen"
                     x-transition:enter="transition ease-in-out duration-300 transform"
                     x-transition:enter-start="-translate-x-full"
                     x-transition:enter-end="translate-x-0"
                     x-transition:leave="transition ease-in-out duration-300 transform"
                     x-transition:leave-start="translate-x-0"
                     x-transition:leave-end="-translate-x-full"
                     class="relative mr-16 flex w-full max-w-xs flex-1">
                    <div class="absolute left-full top-0 flex w-16 justify-center pt-5">
                        <button type="button" class="-m-2.5 p-2.5" @click="sidebarOpen = false">
                            <span class="sr-only">Close sidebar</span>
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Sidebar component -->
                    <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-white px-6 pb-4">
                        <div class="flex h-16 shrink-0 items-center">
                            <svg class="h-8 w-auto text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            <span class="ml-2 text-xl font-bold text-gray-900">ModelPlus</span>
                        </div>
                        <nav class="flex flex-1 flex-col">
                            <ul role="list" class="flex flex-1 flex-col gap-y-7">
                                <li>
                                    <ul role="list" class="-mx-2 space-y-1">
                                        @foreach($models as $m)
                                            <li>
                                                <a href="#" 
                                                   @click.prevent="loadModel('{{ $modelMap[$m] ?? '' }}')"
                                                   class="{{ isset($model) && $m === $model ? 'bg-gray-50 text-indigo-600' : 'text-gray-700 hover:text-indigo-600 hover:bg-gray-50' }} group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold">
                                                    <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" />
                                                    </svg>
                                                    {{ Str::title(Str::plural(Str::snake(class_basename($m), ' '))) }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Static sidebar for desktop -->
        <div class="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-72 lg:flex-col">
            <div class="flex grow flex-col gap-y-5 overflow-y-auto border-r border-gray-200 bg-white px-6 pb-4">
                <div class="flex h-16 shrink-0 items-center">
                    <svg class="h-8 w-auto text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <span class="ml-2 text-xl font-bold text-gray-900">ModelPlus</span>
                </div>
                <nav class="flex flex-1 flex-col">
                    <ul role="list" class="flex flex-1 flex-col gap-y-7">
                        <li>
                            <ul role="list" class="-mx-2 space-y-1">
                                @foreach($models as $m)
                                    <li>
                                        <a href="#" 
                                           @click.prevent="loadModel('{{ $modelMap[$m] ?? '' }}')"
                                           :class="currentModel === '{{ $modelMap[$m] ?? '' }}' ? 'bg-gray-50 text-indigo-600 group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold' : 'text-gray-700 hover:text-indigo-600 hover:bg-gray-50 group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold'">
                                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" />
                                            </svg>
                                            {{ Str::title(Str::plural(Str::snake(class_basename($m), ' '))) }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>

        <div class="lg:pl-72">
            <div class="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-4 border-b border-gray-200 bg-white px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8">
                <button type="button" class="-m-2.5 p-2.5 text-gray-700 lg:hidden" @click="sidebarOpen = true">
                    <span class="sr-only">Open sidebar</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>

                <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
                    @if(isset($model))
                        <form class="relative flex flex-1" @submit.prevent="loadModel('{{ $modelMap[$model] ?? '' }}')">
                            <label for="search-field" class="sr-only">Search</label>
                            <svg class="pointer-events-none absolute inset-y-0 left-0 h-full w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                            </svg>
                            <input id="search-field"
                                   class="block h-full w-full border-0 py-0 pl-8 pr-0 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm"
                                   placeholder="Search..."
                                   type="search"
                                   name="search"
                                   x-model="searchQuery"
                                   @keyup.debounce.300ms="loadModel('{{ $modelMap[$model] ?? '' }}')"
                                   value="{{ request('search') }}">
                        </form>
                    @endif
                </div>
            </div>

            <main class="py-10">
                <div class="px-4 sm:px-6 lg:px-8">
                    <!-- Loading indicator -->
                    <div x-show="loading" class="flex justify-center items-center py-12">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                    </div>
                    
                    <!-- Dynamic content area -->
                    <div x-show="!loading">
                        <div x-html="content">
                            @yield('content')
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html> 