<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <title>Nova Observação - Guardião das Águas</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>

<form action="salvar.php" method="POST" enctype="multipart/form-data">
  <label for="local">Local:</label>
  <input type="text" id="local" name="local" required />

  <label for="data">Data:</label>
  <input type="date" id="data" name="data" required />

  <label for="nivel_agua">Nível da Água:</label>
  <select id="nivel_agua" name="nivel_agua" required>
    <option value="baixa">Baixa</option>
    <option value="normal">Normal</option>
    <option value="cheia">Cheia</option>
  </select>

  <label for="qualidade_agua">Qualidade da Água:</label>
  <select id="qualidade_agua" name="qualidade_agua" required>
    <option value="transparente">Transparente</option>
    <option value="turva">Turva</option>
    <option value="contaminada">Contaminada</option>
  </select>

  <label for="lixo">Lixo visível:</label>
  <select id="lixo" name="lixo" required>
    <option value="nenhum">Nenhum</option>
    <option value="pouco">Pouco</option>
    <option value="muito">Muito</option>
  </select>

  <label for="fauna">Fauna observada:</label>
  <textarea id="fauna" name="fauna"></textarea>

  <label for="foto">Foto (opcional):</label>
  <input type="file" id="foto" name="foto" />

  <button type="submit">Enviar</button>
</form>

</body>
</html>
