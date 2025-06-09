<?php
// Inclui o arquivo de conexão com o banco de dados.
// É essencial que a conexão ($pdo) esteja disponível para inserir os dados.
include 'conexao.php';

// =======================================================================
// PASSO 1: SANITIZAÇÃO E VALIDAÇÃO DOS DADOS RECEBIDOS DO FORMULÁRIO
// =======================================================================
// Por que isso é importante?
// Dados vindos de formulários (input do usuário) nunca são confiáveis.
// Eles podem conter código malicioso (tentativas de XSS ou SQL Injection),
// ou simplesmente dados em formato errado que podem quebrar sua aplicação
// ou o banco de dados.

// filter_input é mais seguro que acessar $_POST diretamente.
// Ele permite filtrar (sanitizar) os dados, removendo ou codificando
// caracteres potencialmente perigosos.

// 1.1. Sanitização de campos de texto
$local = filter_input(INPUT_POST, 'local', FILTER_SANITIZE_STRING); // Remove tags HTML e codifica caracteres especiais
$data = filter_input(INPUT_POST, 'data', FILTER_SANITIZE_STRING);   // Remove tags HTML e codifica caracteres especiais
$nivel = filter_input(INPUT_POST, 'nivel_agua', FILTER_SANITIZE_STRING); // Remove tags HTML e codifica caracteres especiais
$qualidade = filter_input(INPUT_POST, 'qualidade_agua', FILTER_SANITIZE_STRING); // Remove tags HTML e codifica caracteres especiais
$lixo = filter_input(INPUT_POST, 'lixo', FILTER_SANITIZE_STRING);     // Remove tags HTML e codifica caracteres especiais
$fauna = filter_input(INPUT_POST, 'fauna', FILTER_SANITIZE_STRING);   // Remove tags HTML e codifica caracteres especiais

// 1.2. Array para armazenar mensagens de erro.
// Se encontrarmos algum problema, adicionaremos uma mensagem aqui.
$errors = [];

// 1.3. Validações Específicas para cada campo
// Essas validações garantem que os dados estão no formato e dentro dos valores esperados.

// Validação do campo 'local'
if (empty($local)) { // Verifica se o campo está vazio
    $errors[] = "O campo Local é obrigatório.";
} elseif (strlen($local) > 255) { // Exemplo: limita o tamanho da string para não estourar o campo no banco
    $errors[] = "O campo Local é muito longo (máximo 255 caracteres).";
}

// Validação do campo 'data'
if (empty($data)) { // Verifica se o campo está vazio
    $errors[] = "O campo Data é obrigatório.";
} elseif (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $data) || !strtotime($data)) {
    // regex (expressão regular) para verificar o formato AAAA-MM-DD
    // strtotime() tenta converter a string em uma data/hora, retornando false se for inválida
    $errors[] = "Formato de data inválido. Use AAAA-MM-DD.";
} elseif (strtotime($data) > time()) { // strtotime(time()) retorna o timestamp atual
    // Impede que o usuário selecione uma data no futuro para a observação.
    $errors[] = "A data da observação não pode ser futura.";
}

// Validação dos campos 'nivel_agua', 'qualidade_agua', 'lixo'
// Estes são campos de seleção (<select>), então só devem aceitar valores pré-definidos.
$niveisPermitidos = ['baixa', 'normal', 'cheia'];
$qualidadesPermitidas = ['transparente', 'turva', 'contaminada'];
$lixosPermitidos = ['nenhum', 'pouco', 'muito'];

if (!in_array($nivel, $niveisPermitidos)) { // in_array() verifica se o valor está na lista de permitidos
    $errors[] = "Valor inválido para Nível da Água.";
}
if (!in_array($qualidade, $qualidadesPermitidas)) {
    $errors[] = "Valor inválido para Qualidade da Água.";
}
if (!in_array($lixo, $lixosPermitidos)) {
    $errors[] = "Valor inválido para Lixo visível.";
}

// Validação do campo 'fauna'
if (strlen($fauna) > 500) { // Limite de caracteres para descrições mais longas
    $errors[] = "O campo Fauna é muito longo (máximo 500 caracteres).";
}

// =======================================================================
// PASSO 2: TRATAMENTO E VALIDAÇÃO DE UPLOAD DE FOTO
// =======================================================================
// O upload de arquivos é um ponto crítico de segurança.
// É essencial validar o tipo, tamanho e nome do arquivo.

$fotoNome = null; // Inicializa o nome da foto no banco como nulo
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    // Verifica se o arquivo foi enviado corretamente (sem erros de upload do PHP)

    $fileTmpPath = $_FILES['foto']['tmp_name']; // Caminho temporário onde o arquivo foi salvo pelo PHP
    $fileName = $_FILES['foto']['name'];       // Nome original do arquivo enviado pelo usuário
    $fileSize = $_FILES['foto']['size'];       // Tamanho do arquivo em bytes
    $fileType = $_FILES['foto']['type'];       // Tipo MIME do arquivo (ex: 'image/jpeg')
    $fileNameCmps = explode(".", $fileName);   // Divide o nome do arquivo para pegar a extensão
    $fileExtension = strtolower(end($fileNameCmps)); // Pega a última parte (extensão) e converte para minúsculas

    // Definições de segurança para o upload:
    $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg'); // Tipos de arquivo permitidos
    $maxFileSize = 5 * 1024 * 1024; // Tamanho máximo: 5 MB (em bytes)

    // Valida a extensão do arquivo
    if (!in_array($fileExtension, $allowedfileExtensions)) {
        $errors[] = "Tipo de arquivo de foto inválido. Apenas JPG, JPEG, PNG e GIF são permitidos.";
    }
    // Valida o tamanho do arquivo
    if ($fileSize > $maxFileSize) {
        $errors[] = "O tamanho da foto excede o limite de 5MB.";
    }

    // Se não houver erros nas validações do arquivo até agora:
    if (empty($errors)) {
        $newFileName = uniqid() . '-' . $fileName; // Gera um nome único para evitar conflitos (ex: "654321-minha_foto.jpg")
        $uploadFileDir = './uploads/'; // Define o diretório onde as fotos serão salvas (na raiz do projeto)

        // Cria o diretório 'uploads/' se ele não existir.
        // O 0755 define as permissões de escrita/leitura. O 'true' permite a criação de diretórios recursivos.
        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0755, true);
        }

        $dest_path = $uploadFileDir . $newFileName; // Caminho completo para salvar o arquivo

        // Tenta mover o arquivo temporário para o destino final.
        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            $fotoNome = $newFileName; // Se o movimento for bem-sucedido, armazena o nome para o banco de dados
        } else {
            $errors[] = "Erro ao mover o arquivo da foto. Verifique as permissões da pasta 'uploads'.";
        }
    }
} elseif (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
    // Captura outros erros de upload do PHP (ex: tamanho excedido pelo php.ini)
    $errors[] = "Erro no upload da foto: Código do erro (" . $_FILES['foto']['error'] . ").";
}

// =======================================================================
// PASSO 3: VERIFICAÇÃO DE ERROS E EXIBIÇÃO
// =======================================================================
// Se o array $errors não estiver vazio, significa que há problemas com os dados.
if (!empty($errors)) {
    echo "<div style='background-color: #ffe0e0; border: 1px solid #ff4d4d; padding: 15px; margin: 20px auto; width: 80%; max-width: 600px; border-radius: 5px;'>";
    echo "<h2 style='color: #ff4d4d;'>Ops! Encontramos alguns problemas:</h2>";
    echo "<ul style='list-style-type: disc; margin-left: 20px;'>";
    foreach ($errors as $error) {
        // htmlspecialchars() é usado para evitar XSS ao exibir mensagens de erro
        echo "<li style='color: #cc0000; margin-bottom: 5px;'>" . htmlspecialchars($error) . "</li>";
    }
    echo "</ul>";
    echo "<p style='text-align: center; margin-top: 15px;'><a href='nova-observacao.php' style='display: inline-block; padding: 8px 15px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px;'>Voltar ao formulário</a></p>";
    echo "</div>";
    exit; // Importante: Parar a execução do script se houver erros
}

// =======================================================================
// PASSO 4: INSERÇÃO DOS DADOS NO BANCO DE DADOS (APÓS VALIDAÇÃO)
// =======================================================================
// Apenas se não houver NENHUM erro, prosseguimos para o banco.

// Preparando a query SQL para inserção.
// O uso de Prepared Statements com PDO é crucial para prevenir SQL Injection.
// Os '?' são placeholders que serão preenchidos de forma segura pelo execute().
$sql = "INSERT INTO observacoes (local, data, nivel_agua, qualidade_agua, lixo, fauna, foto)
        VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $pdo->prepare($sql); // Prepara a consulta (envia para o BD para pré-compilação)

try {
    // Executa a consulta, passando os valores como um array.
    // O PDO se encarrega de fazer o "escaping" e garantir que os dados sejam seguros.
    $stmt->execute([$local, $data, $nivel, $qualidade, $lixo, $fauna, $fotoNome]);

    // Mensagem de sucesso
    echo "<div style='background-color: #d4edda; border: 1px solid #28a745; padding: 15px; margin: 20px auto; width: 80%; max-width: 600px; border-radius: 5px; text-align: center;'>";
    echo "<h2 style='color: #28a745;'>Observação registrada com sucesso!</h2>";
    echo "<p><a href='index.php' style='display: inline-block; padding: 8px 15px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px;'>Ver Observações</a></p>";
    echo "</div>";

} catch (PDOException $e) {
    // Em caso de erro na inserção no banco de dados.
    // Em um ambiente de produção, você deve registrar o erro em um log (para sua depuração)
    // e exibir uma mensagem genérica para o usuário, por segurança.
    echo "<div style='background-color: #ffe0e0; border: 1px solid #ff4d4d; padding: 15px; margin: 20px auto; width: 80%; max-width: 600px; border-radius: 5px;'>";
    echo "<h2 style='color: #ff4d4d;'>Erro ao registrar observação:</h2>";
    echo "<p style='color: #cc0000;'>Detalhes técnicos: " . htmlspecialchars($e->getMessage()) . "</p>"; // htmlspecialchars para segurança
    echo "<p style='text-align: center; margin-top: 15px;'><a href='nova-observacao.php' style='display: inline-block; padding: 8px 15px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px;'>Voltar ao formulário</a></p>";
    echo "</div>";
}
?>
