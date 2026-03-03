<!-- Footer -->
    <footer class="bg-gray-800 text-white mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="text-center">
                <p>&copy; <?= date('Y') ?> Minimal PHP Framework. Todos os direitos reservados.</p>
                <p class="text-sm text-gray-400 mt-2">PHP 8.4+ • MIT License</p>
            </div>
        </div>
    </footer>
    
    <!-- Scripts -->
    <script>
        // Auto-hide flash messages after 5 seconds
        setTimeout(function() {
            const messages = document.querySelectorAll('[class*="bg-green-100"], [class*="bg-red-100"]');
            messages.forEach(function(msg) {
                msg.style.transition = 'opacity 0.5s';
                msg.style.opacity = '0';
                setTimeout(function() {
                    msg.remove();
                }, 500);
            });
        }, 5000);
    </script>
</body>
</html>
