@php
    if (! isset($scrollTo)) {
        $scrollTo = 'body';
    }
@endphp

<div>
    @if ($paginator->hasPages())
        <nav role="navigation" aria-label="Pagination Navigation"
             class="flex items-center justify-between">

            {{-- モバイル --}}
            <div class="flex justify-between flex-1 sm:hidden">

                {{-- Previous --}}
                <span>
                    @if ($paginator->onFirstPage())
                        <span class="
                            inline-flex items-center
                            px-4 py-2 text-sm
                            text-gray-400 bg-white
                            border border-gray-300
                            rounded-lg cursor-default
                        ">
                            {!! __('pagination.previous') !!}
                        </span>
                    @else
                        <button type="button"
                            wire:click="previousPage('{{ $paginator->getPageName() }}')"
                            class="
                                inline-flex items-center
                                px-4 py-2 text-sm
                                text-gray-700 bg-white
                                border border-gray-300
                                rounded-lg
                                hover:bg-(--color-50)
                                hover:text-(--color-700)
                                focus:outline-none focus:ring-2
                                focus:ring-(--color-500)
                                transition
                            ">
                            {!! __('pagination.previous') !!}
                        </button>
                    @endif
                </span>

                {{-- Next --}}
                <span>
                    @if ($paginator->hasMorePages())
                        <button type="button"
                            wire:click="nextPage('{{ $paginator->getPageName() }}')"
                            class="
                                inline-flex items-center ml-3
                                px-4 py-2 text-sm
                                text-gray-700 bg-white
                                border border-gray-300
                                rounded-lg
                                hover:bg-(--color-50)
                                hover:text-(--color-700)
                                focus:outline-none focus:ring-2
                                focus:ring-(--color-500)
                                transition
                            ">
                            {!! __('pagination.next') !!}
                        </button>
                    @else
                        <span class="
                            inline-flex items-center ml-3
                            px-4 py-2 text-sm
                            text-gray-400 bg-white
                            border border-gray-300
                            rounded-lg cursor-default
                        ">
                            {!! __('pagination.next') !!}
                        </span>
                    @endif
                </span>
            </div>

            {{-- PC --}}
            <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">

                {{-- <p class="text-sm text-gray-600">
                    {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}
                    / {{ $paginator->total() }}
                </p> --}}
                <p class="text-sm text-gray-600">
                    {{ $paginator->total() }}件中
                    {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}件表示
                </p>

                <span class="inline-flex rounded-lg shadow-sm">

                    {{-- Previous --}}
                    @if ($paginator->onFirstPage())
                        <span class="
                            inline-flex items-center
                            px-2 py-2
                            text-gray-400 bg-white
                            border border-gray-300
                            rounded-l-lg cursor-default
                        ">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    @else
                        <button type="button"
                            wire:click="previousPage('{{ $paginator->getPageName() }}')"
                            class="
                                inline-flex items-center
                                px-2 py-2
                                text-gray-600 bg-white
                                border border-gray-300
                                rounded-l-lg
                                hover:bg-(--color-50)
                                hover:text-(--color-700)
                                focus:outline-none focus:ring-2
                                focus:ring-(--color-500)
                            ">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    @endif

                    {{-- Pages --}}
                    @foreach ($elements as $element)
                        @if (is_string($element))
                            <span class="
                                inline-flex items-center
                                px-4 py-2
                                text-sm text-gray-500
                                border-t border-b border-gray-300
                            ">
                                {{ $element }}
                            </span>
                        @endif

                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span class="
                                        inline-flex items-center
                                        px-4 py-2
                                        text-sm font-medium
                                        text-(--color-700)
                                        bg-(--color-50)
                                        border border-gray-300
                                    ">
                                        {{ $page }}
                                    </span>
                                @else
                                    <button type="button"
                                        wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')"
                                        class="
                                            inline-flex items-center
                                            px-4 py-2
                                            text-sm text-gray-600
                                            bg-white
                                            border border-gray-300
                                            hover:bg-(--color-50)
                                            hover:text-(--color-700)
                                            focus:outline-none focus:ring-2
                                            focus:ring-(--color-500)
                                        ">
                                        {{ $page }}
                                    </button>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next --}}
                    @if ($paginator->hasMorePages())
                        <button type="button"
                            wire:click="nextPage('{{ $paginator->getPageName() }}')"
                            class="
                                inline-flex items-center
                                px-2 py-2
                                text-gray-600 bg-white
                                border border-gray-300
                                rounded-r-lg
                                hover:bg-(--color-50)
                                hover:text-(--color-700)
                                focus:outline-none focus:ring-2
                                focus:ring-(--color-500)
                            ">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    @else
                        <span class="
                            inline-flex items-center
                            px-2 py-2
                            text-gray-400 bg-white
                            border border-gray-300
                            rounded-r-lg cursor-default
                        ">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    @endif
                </span>
            </div>
        </nav>
    @endif
</div>
