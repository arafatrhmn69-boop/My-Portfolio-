<?php

declare(strict_types=1);

if (!defined('BASE_PATH')) {
    http_response_code(403);
    exit('Direct access is not allowed.');
}

$footerUser = Auth::user();
?>
            </main>

            <footer class="admin-footer">
                <span>
                    <i class="bi bi-compass" aria-hidden="true"></i>
                    <?= e(APP_NAME) ?> · Admin Console
                </span>
                <span>
                    <?php if ($footerUser !== null): ?>
                        Signed in as <?= e((string) $footerUser['username']) ?> ·
                    <?php endif; ?>
                    Session protected
                </span>
            </footer>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="<?= e(url('admin/assets/admin.js')) ?>" defer></script>
</body>

</html>
