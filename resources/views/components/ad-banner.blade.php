@if($ad)
    <div class="my-6 w-full flex justify-center group/ad-container">
        {{-- Container with Max Width 950px --}}
        <div class="relative overflow-hidden rounded-lg shadow-sm hover:shadow-xl hover:shadow-black/10 transition-all duration-500 w-full max-w-[950px]">
            
            {{-- Label 'Publicidad' --}}
            <div class="absolute top-2 right-2 bg-black/60 backdrop-blur-[2px] text-white/90 text-[10px] px-2 py-0.5 rounded-sm uppercase tracking-wider font-semibold z-20 pointer-events-none shadow-sm border border-white/10">
                Publicidad
            </div>

            {{-- Main Link --}}
            <a href="{{ route('ads.click', $ad) }}" target="_blank" rel="noopener noreferrer" class="block relative w-full h-full">
                
                {{-- Image --}}
                <img src="{{ Storage::url($ad->image_path) }}" 
                     alt="{{ $ad->title }}" 
                     class="w-full h-auto object-cover">
                
                {{-- Gradient Overlay (Always visible but subtle) --}}
                <div class="absolute inset-0 bg-gradient-to-tr from-black/10 via-transparent to-black/5 pointer-events-none z-10"></div>

                {{-- Sheen/Shimmer Animation (Automatic & Continuous) --}}
                <div class="absolute inset-0 -translate-x-[150%] skew-x-[-25deg] bg-gradient-to-r from-transparent via-white/20 to-transparent animate-[shimmer_3s_infinite] z-10 pointer-events-none w-[200%] h-full"></div>
                
                {{-- CTA Pulse Effect (Always visible) --}}
                <div class="absolute bottom-4 right-4 z-20">
                    <span class="flex items-center gap-1.5 bg-white text-black text-xs font-bold px-3 py-1.5 rounded-full shadow-lg transform transition-transform hover:scale-105 active:scale-95">
                        Ver más
                        <svg class="w-3 h-3 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </span>
                </div>
            </a>
        </div>
        
        <style>
            @keyframes shimmer {
                0% { transform: translateX(-150%) skewX(-25deg); }
                30% { transform: translateX(150%) skewX(-25deg); } /* Fast pass */
                100% { transform: translateX(150%) skewX(-25deg); } /* Wait rest of time */
            }
        </style>
    </div>
@endif