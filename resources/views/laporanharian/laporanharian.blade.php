<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Harian</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@300;400;600&display=swap');
        body { font-family: 'Inter', sans-serif; }
        h1, h2 { font-family: 'Playfair Display', serif; }
    </style>
</head>
<body class="bg-gray-100 text-gray-900">

    <div class="max-w-2xl mx-auto px-4 pt-12">
        <!-- Header -->
        <header class="text-center mb-8">
            <h1 class="text-4xl font-bold tracking-wide">Laporan Harian</h1>
            <p class="text-lg text-gray-600 mt-2">Tanggal {{ \Carbon\Carbon::parse($dailyreport->attendance->start_date)->locale('id')->translatedFormat('d F Y') }}</p>
        </header>

        <article class="text-lg leading-relaxed text-gray-800 mt-6">
            <h2 class="font-bold">{{$dailyreport->title}}</h2>
            <h4 class="text-sm text-gray-600 mt-1 mb-2">Dibuat Oleh {{ $dailyreport->user->name }}</h4>
        </article>

        <div class="relative w-full bg-gray-300 rounded-lg overflow-hidden shadow-lg aspect-[16/9] flex items-center justify-center">
            <div id="photo-container" class="flex transition-transform duration-500 ease-in-out w-full h-full">
                <div class="min-w-full flex-shrink-0 flex items-center justify-center">
                    <img src="{{ Storage::url($dailyreport->dokumentasi1) }}" alt="Photo 1" class="max-w-full max-h-full object-contain">
                </div>
                @if ($dailyreport->dokumentasi2)
                <div class="min-w-full flex-shrink-0 flex items-center justify-center">
                    <img src="{{ Storage::url($dailyreport->dokumentasi2) }}" alt="Photo 2" class="max-w-full max-h-full object-contain">
                </div> 
                @endif
            </div>
            @if ($dailyreport->dokumentasi2)
            <!-- Floating Navigation Buttons -->
            <button id="prevBtn" class="absolute left-3 top-1/2 transform -translate-y-1/2 bg-black/50 text-white px-3 py-2 rounded-full hover:bg-black">
                ←
            </button>
            <button id="nextBtn" class="absolute right-3 top-1/2 transform -translate-y-1/2 bg-black/50 text-white px-3 py-2 rounded-full hover:bg-black">
                →
            </button>
            @endif
        </div>
        
        

        <!-- Article-Like Description -->
        <article class="text-md leading-relaxed text-gray-800 mt-6 italic text-justify">
           
                {!! $dailyreport->description !!}

        </article>
    </div>

    <!-- JavaScript for Swipe and Button Navigation -->
    <script>
        let currentIndex = 0;
        const totalImages = 2; // Since you have 2 images
        const photoContainer = document.getElementById("photo-container");

        function updatePhotoPosition() {
            photoContainer.style.transform = `translateX(-${currentIndex * 100}%)`;
        }

        document.getElementById("nextBtn").addEventListener("click", () => {
            if (currentIndex < totalImages - 1) {
                currentIndex++;
                updatePhotoPosition();
            }
        });

        document.getElementById("prevBtn").addEventListener("click", () => {
            if (currentIndex > 0) {
                currentIndex--;
                updatePhotoPosition();
            }
        });

        // Swipe Gesture Handling for Mobile
        let touchStartX = 0;
        let touchEndX = 0;

        photoContainer.addEventListener("touchstart", (e) => {
            touchStartX = e.changedTouches[0].screenX;
        });

        photoContainer.addEventListener("touchend", (e) => {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        });

        function handleSwipe() {
            if (touchStartX - touchEndX > 50) {
                if (currentIndex < totalImages - 1) {
                    currentIndex++;
                    updatePhotoPosition();
                }
            } else if (touchEndX - touchStartX > 50) {
                if (currentIndex > 0) {
                    currentIndex--;
                    updatePhotoPosition();
                }
            }
        }
    </script>

</body>
</html>
