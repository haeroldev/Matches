@extends('layouts.app')

@section('title', 'Add Match')

@section('content')
    <div class="mx-auto max-w-2xl rounded-2xl border border-slate-700 bg-slate-950/60 p-6 shadow-xl shadow-slate-950/30">
        <div class="mb-6">
            <p class="text-sm uppercase tracking-[0.2em] text-amber-400">New Entry</p>
            <h1 class="mt-2 text-3xl font-bold text-white">Save a fight</h1>
        </div>

        <form action="{{ route('matches.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf

            @include('matches.partials.fighter-fields', [
                'fighterPrefix' => 'fighter_1',
                'fighterLabel' => 'Fighter A',
                'fighterName' => old('fighter_1', ''),
                'fighterNameRequired' => true,
                'fighterPalong' => old('fighter_1_palong', false),
                'fighterLambe' => old('fighter_1_lambe', false),
                'fighterLegColor' => old('fighter_1_leg_color', ''),
                'fighterColorBlankLabel' => 'Not specified',
                'fighterNames' => $fighterNames,
            ])

            @include('matches.partials.fighter-fields', [
                'fighterPrefix' => 'fighter_2',
                'fighterLabel' => 'Fighter B',
                'fighterName' => old('fighter_2', ''),
                'fighterNameRequired' => true,
                'fighterPalong' => old('fighter_2_palong', false),
                'fighterLambe' => old('fighter_2_lambe', false),
                'fighterLegColor' => old('fighter_2_leg_color', ''),
                'fighterColorBlankLabel' => 'Not specified',
                'fighterNames' => $fighterNames,
            ])

            <div>
                <label for="winner" class="mb-2 block text-sm font-medium text-slate-200">Winner</label>
                <input id="winner" name="winner" type="text" value="{{ old('winner') }}" class="w-full rounded-xl border border-slate-600 bg-slate-900 px-4 py-3 text-slate-100 outline-none transition focus:border-amber-400 focus:ring-2 focus:ring-amber-500/20" required>
                @error('winner')
                    <p class="mt-1 text-sm text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="image" class="mb-2 block text-sm font-medium text-slate-200">Fight Image</label>
                <input id="image" name="image" type="file" accept="image/*" class="block w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-3 text-sm text-slate-300 file:mr-4 file:rounded-lg file:border-0 file:bg-amber-500 file:px-4 file:py-2 file:font-semibold file:text-slate-950 hover:file:bg-amber-400">
                <input type="hidden" id="croppedImageData" name="cropped_image_data" value="">
                @error('image')
                    <p class="mt-1 text-sm text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <div id="imagePreviewWrap" class="hidden">
                <p class="mb-2 text-sm font-medium text-slate-200">Image Preview</p>
                <img id="imagePreview" src="" alt="Fight preview" class="h-56 w-full rounded-xl border border-slate-700 object-cover">
            </div>

            <div id="cropperBox" class="hidden rounded-2xl border border-slate-700 bg-slate-900/60 p-4">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-white">Crop & Resize</p>
                        <p class="text-xs text-slate-400">Adjust the image before saving.</p>
                    </div>
                    <button type="button" id="applyCrop" class="rounded-lg bg-amber-500 px-3 py-2 text-xs font-semibold text-slate-950 hover:bg-amber-400">
                        Apply Crop
                    </button>
                </div>

                <canvas id="cropCanvas" class="w-full rounded-xl border border-slate-700 bg-slate-950" width="1200" height="800"></canvas>

                <div class="mt-4 grid gap-3 md:grid-cols-3">
                    <label class="block text-xs uppercase tracking-[0.2em] text-slate-400">
                        Zoom
                        <input id="cropZoom" type="range" min="1" max="3" step="0.1" value="1" class="mt-2 w-full">
                    </label>

                    <label class="block text-xs uppercase tracking-[0.2em] text-slate-400">
                        Horizontal
                        <input id="cropX" type="range" min="0" max="100" step="1" value="50" class="mt-2 w-full">
                    </label>

                    <label class="block text-xs uppercase tracking-[0.2em] text-slate-400">
                        Vertical
                        <input id="cropY" type="range" min="0" max="100" step="1" value="50" class="mt-2 w-full">
                    </label>
                </div>

                <p id="cropStatus" class="mt-3 text-xs text-emerald-400">Select an image to begin cropping.</p>
            </div>

            <div>
                <label for="description" class="mb-2 block text-sm font-medium text-slate-200">Fight Description</label>
                <textarea id="description" name="description" rows="4" class="w-full rounded-xl border border-slate-600 bg-slate-900 px-4 py-3 text-slate-100 outline-none transition focus:border-amber-400 focus:ring-2 focus:ring-amber-500/20" placeholder="Describe the matchup, fighter styles, or key story behind the fight." required>{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between gap-4 pt-4">
                <a href="{{ route('matches.index') }}" class="rounded-xl border border-slate-600 px-4 py-2.5 font-medium text-slate-200 transition hover:bg-slate-800">
                    Cancel
                </a>
                <button type="submit" class="rounded-xl bg-amber-500 px-5 py-2.5 font-semibold text-slate-950 transition hover:bg-amber-400">
                    Save Match
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const imageInput = document.getElementById('image');
            const imagePreviewWrap = document.getElementById('imagePreviewWrap');
            const imagePreview = document.getElementById('imagePreview');
            const cropperBox = document.getElementById('cropperBox');
            const cropCanvas = document.getElementById('cropCanvas');
            const cropZoom = document.getElementById('cropZoom');
            const cropX = document.getElementById('cropX');
            const cropY = document.getElementById('cropY');
            const applyCropBtn = document.getElementById('applyCrop');
            const cropStatus = document.getElementById('cropStatus');
            const croppedImageData = document.getElementById('croppedImageData');
            const canvasContext = cropCanvas.getContext('2d');
            let currentImage = null;
            let objectUrl = null;

            function updatePreview(file) {
                if (!file) {
                    imagePreviewWrap.classList.add('hidden');
                    imagePreview.src = '';
                    return;
                }

                const previewUrl = URL.createObjectURL(file);
                imagePreview.src = previewUrl;
                imagePreviewWrap.classList.remove('hidden');
            }

            function drawCropPreview() {
                if (!currentImage) {
                    return;
                }

                const width = currentImage.width;
                const height = currentImage.height;
                const zoom = Number(cropZoom.value);
                const targetWidth = 1200;
                const targetHeight = 800;
                const cropRatio = targetWidth / targetHeight;

                const cropWidth = width / zoom;
                const cropHeight = cropWidth / cropRatio;

                const maxX = Math.max(0, width - cropWidth);
                const maxY = Math.max(0, height - cropHeight);
                const sourceX = (Number(cropX.value) / 100) * maxX;
                const sourceY = (Number(cropY.value) / 100) * maxY;

                cropCanvas.width = targetWidth;
                cropCanvas.height = targetHeight;
                canvasContext.clearRect(0, 0, targetWidth, targetHeight);
                canvasContext.fillStyle = '#0f172a';
                canvasContext.fillRect(0, 0, targetWidth, targetHeight);
                canvasContext.drawImage(currentImage, sourceX, sourceY, cropWidth, cropHeight, 0, 0, targetWidth, targetHeight);
            }

            function replaceInputFile(blob) {
                const file = new File([blob], `match-cropped-${Date.now()}.jpg`, { type: 'image/jpeg' });
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                imageInput.files = dataTransfer.files;
                updatePreview(file);
            }

            imageInput.addEventListener('change', function (event) {
                const [file] = event.target.files;
                if (!file) {
                    updatePreview(null);
                    return;
                }

                croppedImageData.value = '';

                if (objectUrl) {
                    URL.revokeObjectURL(objectUrl);
                }

                objectUrl = URL.createObjectURL(file);
                updatePreview(file);

                const img = new Image();
                img.onload = function () {
                    currentImage = img;
                    cropperBox.classList.remove('hidden');
                    cropZoom.value = '1';
                    cropX.value = '50';
                    cropY.value = '50';
                    drawCropPreview();
                    cropStatus.textContent = 'Crop preview ready.';
                };
                img.src = objectUrl;
            });

            [cropZoom, cropX, cropY].forEach(function (input) {
                input.addEventListener('input', drawCropPreview);
            });

            applyCropBtn.addEventListener('click', function () {
                if (!currentImage) {
                    return;
                }

                cropCanvas.toBlob(function (blob) {
                    if (!blob) {
                        cropStatus.textContent = 'Unable to create cropped image. Please try again.';
                        return;
                    }

                    const croppedUrl = cropCanvas.toDataURL('image/jpeg', 0.92);
                    croppedImageData.value = croppedUrl;
                    imageInput.value = '';
                    imagePreview.src = croppedUrl;
                    imagePreviewWrap.classList.remove('hidden');
                    replaceInputFile(blob);
                    cropperBox.classList.add('hidden');
                    cropStatus.textContent = 'Cropping applied. Ready to upload.';
                }, 'image/jpeg', 0.92);
            });
        });
    </script>
@endsection
