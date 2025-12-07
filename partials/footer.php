    <!-- Footer Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/ethers@5.7.2/dist/ethers.umd.min.js"></script>
    <script src="contract.js"></script>
    <script>
        // Initialize wallet status on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Update wallet status after a short delay to allow contract.js to initialize
            setTimeout(function() {
                if (typeof updateWalletStatus === 'function') {
                    updateWalletStatus();
                }
            }, 500);
        });
    </script>
</body>
</html>

