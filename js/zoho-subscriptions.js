$( document ).ready(function() {

    $('#invoice-download').on('click', function(event) {
        event.preventDefault();//prevent default so the a's href doesn't send us to the image directly
        window.location.href = $(this).attr('href');
    });
    $('#btn-pay-now').on('click', function(event) {
        event.preventDefault();//prevent default so the a's href doesn't send us to the image directly
        window.location.href = $(this).attr('href');
    });
});