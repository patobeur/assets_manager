<h1 class="text-3xl font-bold mb-6">Emprunter du matériel</h1>

<?php if (isset($success)): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline"><?php echo $success; ?></span>
    </div>
<?php endif; ?>
<?php if (isset($error)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline"><?php echo $error; ?></span>
    </div>
<?php endif; ?>


<div class="max-w-md bg-white p-8 border border-gray-300 rounded">
    <form action="?page=loans" method="post">
        <div class="mb-4">
            <label for="student_barcode" class="block text-gray-700 text-sm font-bold mb-2">Code-barres de l'étudiant</label>
            <input type="text" id="student_barcode" name="student_barcode" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required autofocus>
        </div>
        <div class="mb-6">
            <label for="material_barcode" class="block text-gray-700 text-sm font-bold mb-2">Code-barres du matériel</label>
            <input type="text" id="material_barcode" name="material_barcode" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Emprunter
            </button>
        </div>
    </form>
</div>