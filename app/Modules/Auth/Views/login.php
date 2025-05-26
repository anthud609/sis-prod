<form class="max-w-sm mx-auto mt-10 p-8 bg-white rounded shadow" method="POST" action="/login">
    <h2 class="text-2xl font-bold mb-6 text-center">Login</h2>
    <div class="mb-4">
        <label class="block text-gray-700 mb-2" for="email">Email</label>
        <input class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" type="email" id="email" name="email" required>
    </div>
    <div class="mb-6">
        <label class="block text-gray-700 mb-2" for="password">Password</label>
        <input class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" type="password" id="password" name="password" required>
    </div>
    <button class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition" type="submit">Login</button>
</form>