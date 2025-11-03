<div id="clear-credentials-overlay" 
    class="fixed inset-0 bg-gray-800/40 backdrop-blur-sm z-101 flex items-end justify-end p-4">

    <form id="clear-credentials-form" 
        class="w-80 bg-white shadow-lg rounded-xl p-6 text-sm text-gray-700 space-y-4 z-102">
        <h1 class="text-2xl font-semibold text-center text-gray-800">
            @lang('CLEARING ALL CREDENTIALS')
        </h1>
        <p>
            @lang('If you delete all credentials, you will then need to recreate the administrator credentials to access the Supercontrols panel.')
        </p>
        <p>
            @lang('To confirm, enter your Secret Code in the field below.')
        </p>
        <div>
            <label for="secret-hash" 
                class="block text-sm font-medium text-gray-700">
                    @lang('The secret code')
            </label>
            <input id="secret-hash" type="password" 
                name="@lang("secret-code")" autocomplete="off" autofocus
                class="mt-1 w-full rounded-md border-gray-300 focus:ring-2 
                focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div class="flex items-center space-x-2">
            <input id="password-toggle" type="checkbox"
                class="text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
            <label for="password-toggle" class="text-sm text-gray-600">
                @lang('Show the hidden texts')
            </label>
        </div>
        <button type="submit" id='clear-credentials-btn' 
            class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 transition">
            @lang('Send')
        </button>
        <input type="hidden" name="user-verb" value="clear-credentials">
    </form>
</div>
