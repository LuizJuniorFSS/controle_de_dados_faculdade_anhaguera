<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';

/**
 * Função para limpar e validar dados de entrada
 * @param string $data Dados a serem limpos
 * @return string Dados limpos
 */
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Função para redirecionar para outra página
 * @param string $url URL para redirecionamento
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Função para exibir mensagem de alerta
 * @param string $message Mensagem a ser exibida
 * @param string $type Tipo de alerta (success, danger, warning, info)
 * @return string HTML do alerta
 */
function showAlert($message, $type = 'info') {
    return '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">
        ' . $message . '
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';
}

/**
 * Função para verificar se o usuário está logado
 * @return bool True se estiver logado, False caso contrário
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Função para formatar data no padrão brasileiro
 * @param string $date Data no formato Y-m-d
 * @return string Data no formato d/m/Y
 */
function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}

/**
 * Função para formatar valor monetário
 * @param float $value Valor a ser formatado
 * @return string Valor formatado
 */
function formatMoney($value) {
    return 'R$ ' . number_format($value, 2, ',', '.');
}

/**
 * Função para gerar URL amigável (slug)
 * @param string $string String a ser convertida
 * @return string Slug gerado
 */
function generateSlug($string) {
    $string = preg_replace('/[áàãâä]/ui', 'a', $string);
    $string = preg_replace('/[éèêë]/ui', 'e', $string);
    $string = preg_replace('/[íìîï]/ui', 'i', $string);
    $string = preg_replace('/[óòõôö]/ui', 'o', $string);
    $string = preg_replace('/[úùûü]/ui', 'u', $string);
    $string = preg_replace('/[ç]/ui', 'c', $string);
    $string = preg_replace('/[^a-z0-9]/i', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return strtolower(trim($string, '-'));
}

/**
 * Função para obter o nome da página atual
 * @return string Nome da página atual
 */
function getCurrentPage() {
    $path = $_SERVER['PHP_SELF'];
    $pathInfo = pathinfo($path);
    return $pathInfo['filename'];
}

/**
 * Função para verificar se a página atual é a especificada
 * @param string $page Nome da página a ser verificada
 * @return bool True se for a página atual, False caso contrário
 */
function isCurrentPage($page) {
    return getCurrentPage() == $page;
}

/**
 * Função para obter o endereço IP do usuário
 * @return string Endereço IP
 */
function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

/**
 * Função para registrar log de atividade
 * @param string $action Ação realizada
 * @param string $details Detalhes da ação
 */
function logActivity($action, $details = '') {
    // Implementar registro de log se necessário
    // Por exemplo, salvar em arquivo ou banco de dados
}

/**
 * Função para converter valor monetário do formato brasileiro para o formato do banco de dados
 * @param string $value Valor no formato brasileiro (ex: 1.234,56)
 * @return float Valor no formato para banco de dados (ex: 1234.56)
 */
function convertMoneyToFloat($value) {
    // Remove qualquer caractere que não seja número, vírgula ou ponto
    $value = preg_replace('/[^0-9,.]/', '', $value);
    
    // Se tiver vírgula e ponto, assume formato brasileiro (1.234,56)
    if (strpos($value, ',') !== false && strpos($value, '.') !== false) {
        // Remove os pontos (separadores de milhar)
        $value = str_replace('.', '', $value);
        // Substitui a vírgula por ponto (separador decimal)
        $value = str_replace(',', '.', $value);
    } 
    // Se tiver apenas vírgula, substitui por ponto
    elseif (strpos($value, ',') !== false) {
        $value = str_replace(',', '.', $value);
    }
    
    return (float) $value;
}
?>