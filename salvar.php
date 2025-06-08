<?php
include 'conexao.php';

$local = $_POST['local'];
$data = $_POST['data'];
$nivel = $_POST['nivel_agua'];
$qualidade = $_POST['qualidade_agua'];
$lixo = $_POST['lixo'];
$fauna = $_POST['fauna'];

$fotoNome = null;
if (!empty($_FILES['foto']['tmp_name'])) {
    $fotoNome = uniqid() . '-' . $_FILES['foto']['name'];
    move_uploaded_file($_FILES['foto']['tmp_name'], "uploads/" . $fotoNome);
}

$sql = "INSERT INTO observacoes (local, data, nivel_agua, qualidade_agua, lixo, fauna, foto)
        VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $pdo->prepare($sql);
$stmt->execute([$local, $data, $nivel, $qualidade, $lixo, $fauna, $fotoNome]);

echo "Observação registrada com sucesso.";
