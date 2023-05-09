/*
    Авторизация
 */

$('.login-btn').click(function (e) {
    e.preventDefault();

    $(`input`).removeClass('error');

    let login = $('input[name="login"]').val(),
        password = $('input[name="password"]').val(),
        captcha = grecaptcha.getResponse();

    if (!captcha.length) {
        $('.msg').removeClass('none').text('Вы не прошли проверку "Я не робот"');
    }

    if (captcha.length) {
        $.ajax({
            url: '/auth/sign',
            type: 'POST',
            dataType: 'json',
            data: {
                login: login,
                password: password,
                recaptcha_response: captcha
            },
            success(data) {
                if (data.status) {
                    document.location.href = '/';
                } else {
                    grecaptcha.reset();
                    if (data.type === 1) {
                        data.fields.forEach(function (field) {
                            $(`input[name="${field}"]`).addClass('error');
                        });
                    }

                    $('.msg').removeClass('none').text(data.message);
                }

            }
        });
    }

});

/*
    Регистрация
 */

$('.register-btn').click(function (e) {
    e.preventDefault();

    $(`input`).removeClass('error');

    let login = $('input[name="login"]').val();
    let password = $('input[name="password"]').val();
    let full_name = $('input[name="full_name"]').val();
    let email = $('input[name="email"]').val();
    let password_confirm = $('input[name="password_confirm"]').val();

    let captcha = grecaptcha.getResponse();
    if (!captcha.length) {
        $('.msg').removeClass('none').text('Вы не прошли проверку "Я не робот"');
    }

    if (captcha.length) {
        $.ajax({
            url: '/auth/register',
            type: 'POST',
            dataType: 'json',
            data: {
                login: login,
                password: password,
                full_name: full_name,
                email: email,
                password_confirm: password_confirm,
                recaptcha_response: captcha,
            },
            success(data) {

                if (data.status) {
                    document.location.href = '/auth/sign';
                } else {
                    grecaptcha.reset();
                    if (data.type === 1) {
                        data.fields.forEach(function (field) {
                            $(`input[name="${field}"]`).addClass('error');
                        });
                    }

                    $('.msg').removeClass('none').text(data.message);
                }

            }
        });
    }
});
