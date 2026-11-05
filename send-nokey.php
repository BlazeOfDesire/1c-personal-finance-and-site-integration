<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (!empty($_POST['website_url'])) {

        http_response_code(200);
        echo "Спасибо! Ваша заявка успешно отправлена.";
        exit;
    }
    
    $form_time = (int)($_POST['form_time'] ?? 0);
    $current_time = time() * 1000;
    
    if ($form_time > 0 && ($current_time - $form_time < 3000)) {
        http_response_code(400);
        echo "Слишком быстро. Попробуйте ещё раз.";
        exit;
    }
    
    $fio_raw = trim($_POST['fio'] ?? '');
    $email_raw = trim($_POST['email'] ?? '');
    $number_raw = trim($_POST['number'] ?? '');
    $city_raw = trim($_POST['city'] ?? '');
    $NameOfLLC_raw = trim($_POST['NameOfLLC'] ?? '');
    $RequestedService_raw = trim($_POST['RequestedService'] ?? '');
    $NumberOfEmployees_raw = trim($_POST['NumberOfEmployees'] ?? '');
    $SystemTax_raw = trim($_POST['SystemTax'] ?? '');
    
    if (preg_match('/<[^>]*>/', $fio_raw) || preg_match('/<[^>]*>/', $email_raw) || 
        preg_match('/<[^>]*>/', $city_raw) || preg_match('/<[^>]*>/', $NameOfLLC_raw)) {
        http_response_code(400);
        echo "Некорректные данные";
        exit;
    }
    
    if (!preg_match('/^[а-яА-ЯёЁa-zA-Z\s\-]{3,100}$/u', $fio_raw)) {
        http_response_code(400);
        echo "ФИО должно содержать только буквы (от 3 до 100 символов)";
        exit;
    }
    
    $email = filter_var($email_raw, FILTER_SANITIZE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo "Некорректный email";
        exit;
    }
    
    if (!preg_match('/^[\d\+\-\(\)\s]{10,20}$/', $number_raw)) {
        http_response_code(400);
        echo "Некорректный телефон (10-20 символов)";
        exit;
    }
    
    if (!preg_match('/^[а-яА-ЯёЁa-zA-Z\s]{2,50}$/u', $city_raw)) {
        http_response_code(400);
        echo "Некорректный город";
        exit;
    }
    
    if (!preg_match('/^[а-яА-ЯёЁa-zA-Z0-9\s\-\"]{2,150}$/u', $NameOfLLC_raw)) {
        http_response_code(400);
        echo "Некорректное название компании";
        exit;
    }
    
    $allowedServices = [
        "Бухгалтерский учет",
        "Отчетность",
        "Нулевая отчетность",
        "Экспресс-аудит",
        "Расчет заработной платы",
        "Кадровый учет",
        "Оптимизация налогообложения",
        "Восстановление бухучета"
    ];
    if (!in_array($RequestedService_raw, $allowedServices)) {
        http_response_code(400);
        echo "Некорректная услуга";
        exit;
    }
    
    if (!empty($NumberOfEmployees_raw) && !preg_match('/^\d{1,5}$/', $NumberOfEmployees_raw)) {
        http_response_code(400);
        echo "Некорректное количество работников";
        exit;
    }

    $allowedTaxSystems = [
        "УСН Доходы",
        "Патент",
        "ЕНВД",
        "УСН Доходы - расходы",
        "ОСН",
        "УСН Доходы + ЕНВД",
        "Патент + ОСН",
        "Патент + УСН Доходы",
        "Патент + УСН Доходы - расходы",
        "УСН Доходы - расходы + ЕНВД",
        "ОСН+ЕНВД"
    ];
    if (!in_array($SystemTax_raw, $allowedTaxSystems)) {
        http_response_code(400);
        echo "Некорректная система налогообложения";
        exit;
    }
    
    $fio = htmlspecialchars($fio_raw, ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    $number = htmlspecialchars($number_raw, ENT_QUOTES, 'UTF-8');
    $city = htmlspecialchars($city_raw, ENT_QUOTES, 'UTF-8');
    $NameOfLLC = htmlspecialchars($NameOfLLC_raw, ENT_QUOTES, 'UTF-8');
    $RequestedService = htmlspecialchars($RequestedService_raw, ENT_QUOTES, 'UTF-8');
    $NumberOfEmployees = htmlspecialchars($NumberOfEmployees_raw, ENT_QUOTES, 'UTF-8');
    $SystemTax = htmlspecialchars($SystemTax_raw, ENT_QUOTES, 'UTF-8');
    
    if (empty($fio) || empty($email) || empty($number) || empty($city) || empty($NameOfLLC)) {
        http_response_code(400);
        echo "Заполните все обязательные поля (отмечены *)";
        exit;
    }
    
    $to = 'info@бухнавигатор.рф';
    $subject = 'Новая заявка с сайта';
    
    $message = "
    <html>
    <head>
        <meta charset='utf-8'>
        <style>
            body { font-family: Arial, sans-serif; }
            table { border-collapse: collapse; width: 100%; }
            td { padding: 8px; border-bottom: 1px solid #ddd; }
            .label { font-weight: bold; width: 40%; }
        </style>
    </head>
    <body>
        <h2>Новая заявка с сайта</h2>
        <table>
            <tr>
                <td class='label'>ФИО:</td>
                <td>{$fio}</td>
            </tr>
            <tr>
                <td class='label'>Email:</td>
                <td>{$email}</td>
            </tr>
            <tr>
                <td class='label'>Телефон:</td>
                <td>{$number}</td>
            </tr>
            <tr>
                <td class='label'>Город:</td>
                <td>{$city}</td>
            </tr>
            <tr>
                <td class='label'>Название ООО/ИП:</td>
                <td>{$NameOfLLC}</td>
            </tr>
            <tr>
                <td class='label'>Запрашиваемая услуга:</td>
                <td>{$RequestedService}</td>
            </tr>
            <tr>
                <td class='label'>Количество работников:</td>
                <td>{$NumberOfEmployees}</td>
            </tr>
            <tr>
                <td class='label'>Система налогообложения:</td>
                <td>{$SystemTax}</td>
            </tr>
        </table>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=utf-8\r\n";
    $headers .= "From: Сайт <info@бухнавигатор.рф>\r\n";
    $headers .= "Reply-To: {$email}\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    if (mail($to, $subject, $message, $headers)) {
        http_response_code(200);
        echo "Спасибо! Ваша заявка успешно отправлена.";
    } else {
        http_response_code(500);
        echo "Ошибка при отправке заявки. Попробуйте позже или свяжитесь с нами по телефону.";
    }
    
} else {
    http_response_code(403);
    echo "Некорректный запрос";
}

$apiUrl = 'https://бухнавигатор.рф/send-zayavki.php?action=add&key=тутворигиналеключесть';

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($_POST));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);

if (curl_errno($ch)) {
    error_log('Ошибка сохранения заявки: ' . curl_error($ch));
}

curl_close($ch);
?> 