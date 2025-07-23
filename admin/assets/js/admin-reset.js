document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.wizewpph-reset-button').forEach(button => {
        button.addEventListener('click', function () {
            if (!confirm('Are you sure you want to reset price history for this product?')) {
                return;
            }

            const productId = this.dataset.productId;
            const resultContainer = this.nextElementSibling;

            resultContainer.innerHTML = 'Processing...';

            fetch(ajaxurl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'wizewpph_reset_product',
                    product_id: productId,
                    nonce: wizewpph_reset_vars.nonce
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    resultContainer.innerHTML = '<span style="color:green;">Reset successfully.</span>';
                } else {
                    resultContainer.innerHTML = '<span style="color:red;">' + (data.data || 'Error') + '</span>';
                }
            })
            .catch(err => {
                resultContainer.innerHTML = '<span style="color:red;">Request failed.</span>';
            });
        });
    });
});
