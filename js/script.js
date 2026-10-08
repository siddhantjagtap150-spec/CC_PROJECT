document.addEventListener('DOMContentLoaded', function () {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.25s ease';
            setTimeout(function () {
                alert.remove();
            }, 250);
        }, 5000);
    });
});
