// --- Mostrar toast si backend envía mensaje ---
<?php if ($mensajeToast): ?>
    mostrarToast("<?php echo htmlspecialchars($mensajeToast); ?>", "<?php echo $tipoToast; ?>");
    <?php if ($tipoToast === 'success'): ?>
    setTimeout(()=>{ window.location.href = 'denuncia_anonima.php'; }, 4000);
    <?php endif; ?>
<?php endif; ?>
