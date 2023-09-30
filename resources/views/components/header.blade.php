<div class="bg-primary text-white">
    <div class="container mx-auto py-4">
        <div class="flex items-center justify-between">
            <!-- Logo and Profile Picture -->
            <div class="flex items-center">
                <a href="{{ route('profile-show') }}" class="flex items-center space-x-2">
                    <div class="w-12 h-12 overflow-hidden rounded-full">
                        <img src="{{ Auth::user()->profile_picture ? Storage::url(Auth::user()->profile_picture) : asset('images/default-profile.jpeg') }}"
                            alt="Profile Picture" class="w-full h-full object-cover" />
                    </div>

                </a>
            </div>
            <!-- Navigation Links -->

        </div>
    </div>
</div>
