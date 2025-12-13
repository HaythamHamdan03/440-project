    <!-- Footer Scripts -->
    <!-- Note: ethers.js, wallet.js, and blockchain.js are loaded in header.php -->
    <script>
        // Initialize wallet status on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Update wallet status after a short delay to allow scripts to initialize
            setTimeout(function() {
                if (typeof updateWalletButton === 'function') {
                    updateWalletButton();
                }
                // Check for existing wallet connection
                if (typeof checkExistingConnection === 'function') {
                    checkExistingConnection();
                }
            }, 500);
        });
    </script>
</body>
</html>

