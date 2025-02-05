<div>
    <div class="container mx-auto max-w-sm">
         <div class="bg-white p-6 rounded-lg mt-3 shadow-lg">
             <div class="grid grid-cols-1 gap-6 mb-6">
                 <div>
                     <h2 class="text-2xl font-bold mb-2">Informasi Pegawai</h2>
                     <div class="bg-gray-100 p-4 rounded-lg">
                         <p><strong>Nama Pegawai : </strong> {{Auth::user()->name}}</p>
                     </div>

                     <div class="bg-gray-100 p-4 rounded-lg shadow-md">
                        <label for="scheduleSelect" class="block text-sm font-semibold text-gray-700 mb-2">
                            <strong>Jadwal :</strong>
                        </label>
                        <div class="relative">
                            <select wire:model.live="selectedshiftschedule" id="scheduleSelect"
                                class="block w-full appearance-none bg-white border border-gray-300 text-gray-700 py-3 px-4 pr-10 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                                <option value="">-- Pilih Jadwal --</option>
                                @foreach($schedules as $shiftschedule)
                                    <option value="{{ $shiftschedule->id }}">
                                        {{ $shiftschedule->shift->name }} ({{ $shiftschedule->shift->start_time }} - {{ $shiftschedule->shift->end_time }})
                                    </option>
                                @endforeach
                            </select>
                            <!-- Dropdown Icon -->
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                    </div>

                     <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                         <div class="bg-gray-100 p-4 rounded-lg">
                             <h4 class="text-l font-bold mb-2">Waktu Datang</h4>
                            @if ($attendance && $attendance->end_time == NULL)
                                <p><strong>{{$attendance ? $attendance->start_time : '-'}}</p>
                            @endif
                         </div>
                         <div class="bg-gray-100 p-4 rounded-lg">
                             <h4 class="text-l font-bold mb-2">Waktu Pulang</h4>
                            @if ($attendance && $attendance->end_time == NULL)
                                <p><strong>{{$attendance ? $attendance->end_time : '-'}}</p>
                            @endif
                         </div>
                     </div>
                 </div>

                 @if (session()->has('error'))
                    <div style="color: red; padding: 10px; border: 1px solid red; background-color: #fdd;" class="mb-4">
                        {{ session('error') }}
                    </div>
                @endif
 
                 <div>
                     <h2 class="text-2xl font-bold mb-2">Presensi</h2>
                   
                        <div id="map" class="mb-4 rounded-lg border border-gray-300" wire:ignore></div>
                    

                 
                     @if ($selectedshiftschedule)
                        <button type="button" onclick="tagLocation()" class="px-4 py-2 bg-blue-500 text-white rounded">Tag Location</button>
                        @if ($insideRadius)
                            <button class="px-4 py-2 bg-green-500 text-white rounded" wire:loading.attr="disabled" wire:click='store_start'>Masuk</button>
                            <button class="px-4 py-2 text-white rounded" style="background-color: red;" wire:loading.attr="disabled" wire:click='store_end'>Pulang</button>
                        @endif
                     @endif
                     
                     {{-- <form class="row g-3 mt-4" wire:submit="store_start" enctype="multipart/form-data">
                         @csrf                         
                         @if($insideRadius)
                            <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded" wire:loading.attr="disabled">
                                Masuk
                            </button>
                         @endif
                     </form>
                     <form class="row g-3 mt-4" wire:submit="store_end" enctype="multipart/form-data">
                        @csrf                         
                        @if($insideRadius)
                           <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded" wire:loading.attr="disabled">
                               Pulang
                           </button>
                        @endif
                    </form> --}}
                 </div>
 
             </div>
         </div>
         
 
    </div>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
         let map;
         let lat;
         let lng;
         const office = [{{$shiftschedule->office->latitude}}, {{$shiftschedule->office->longitude}}];
         const radius = {{$shiftschedule->office->radius}};
         let component;
         let marker;

        


         document.addEventListener('livewire:initialized', function() {
             component = @this;
             map = L.map('map').setView([{{$shiftschedule->office->latitude}}, {{$shiftschedule->office->longitude}}], 15);
             L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
 
             const circle = L.circle(office, {
                 color: 'red',
                 fillColor: '#f03',
                 fillOpacity: 0.5,
                 radius: radius
             }).addTo(map);
         })
         
 
         let accuracyCircle;

         function tagLocation() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        function (position) {
                            lat = position.coords.latitude;
                            lng = position.coords.longitude;
                            let accuracy = position.coords.accuracy; // Accuracy in meters

                            // Check for Fake GPS Usage
                            if (accuracy < 7) {
                                showFakeGPSWarning();
                                component.set('insideRadius', false); // Disable check-in button
                                return;
                            }

                            if (marker) {
                                map.removeLayer(marker);
                            }

                            marker = L.marker([lat, lng]).addTo(map);
                            map.setView([lat, lng], 15);

                            // Remove old accuracy circle
                            if (accuracyCircle) {
                                map.removeLayer(accuracyCircle);
                            }

                            // Add new accuracy circle
                            accuracyCircle = L.circle([lat, lng], {
                                color: 'blue',
                                fillColor: '#add8e6',
                                fillOpacity: 0.4,
                                radius: accuracy // Use the accuracy value as radius
                            }).addTo(map);

                            if (isWithinRadius(lat, lng, office, radius)) {
                                component.set('insideRadius', true);
                                component.set('latitude', lat);
                                component.set('longitude', lng);
                                component.set('accuracy', accuracy);
                            }
                        },
                        function (error) {
                            alert(`Gagal mendapatkan lokasi: ${error.message}`);
                        },
                        {
                            enableHighAccuracy: true,
                            timeout: 10000,
                            maximumAge: 0
                        }
                    );
                } else {
                    alert('Geolocation tidak didukung di browser ini.');
                }
            }
            // Function to show Fake GPS warning
            function showFakeGPSWarning() {
                let existingWarning = document.getElementById("fakeGPSWarning");
                if (existingWarning) {
                    existingWarning.remove();
                }

                const warningDiv = document.createElement('div');
                warningDiv.id = "fakeGPSWarning";
                warningDiv.innerHTML = `
                    <div style="color: red; padding: 10px; border: 1px solid red; background-color: #fdd; text-align: center; margin-top: 10px; border-radius: 5px;">
                        <strong>Kamu Dicurigai Menggunakan Fake GPS, coba ulang lagi!</strong>
                    </div>
                `;

                // Insert warning above the form
                const form = document.querySelector("form");
                form.parentNode.insertBefore(warningDiv, form);
            }
 
         function isWithinRadius(lat, lng, center, radius) {
             
                 let distance = map.distance([lat, lng], center);
                 return distance <= radius;
             
             
         }
 
 
 
     </script>
 
 </div>
 