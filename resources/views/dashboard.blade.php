<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-green-400 dark:text-green-300 leading-tight">
            {{ __('Welcome to IUT SIKS!') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gradient-to-br from-green-900 via-green-800 to-green-700 min-h-screen">
        <div class="max-w-3xl mx-auto px-4">
            <div class="bg-gray-900 rounded-lg shadow-lg p-10">
                <h1 class="text-5xl font-extrabold text-green-300 mb-6 text-center drop-shadow-lg tracking-tight">IUT SIKS</h1>
                <p class="text-xl text-green-100 mb-8 text-center max-w-2xl mx-auto">
                    Fostering spiritual growth and academic excellence at <span class="text-green-400 font-bold">Islamic University of Technology</span>
                </p>
                <div class="flex flex-col md:flex-row justify-center gap-6 mb-10">
                    <a href="#" class="bg-gradient-to-r from-green-500 to-green-700 text-white font-bold px-8 py-4 rounded-full shadow-lg hover:from-green-400 hover:to-green-600 transition-all duration-300 text-center">
                        Join Our Community
                    </a>
                    <a href="#" class="border-2 border-green-400 text-green-300 font-bold px-8 py-4 rounded-full shadow-lg hover:bg-green-400 hover:text-white transition-all duration-300 text-center">
                        Explore Events
                    </a>
                </div>
                <div class="flex justify-center mt-8">
                    <a href="{{ route('blogs.index') }}" class="inline-block bg-green-700 hover:bg-green-600 text-white px-6 py-3 rounded shadow text-lg font-semibold">
                        Go to Blogs
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
