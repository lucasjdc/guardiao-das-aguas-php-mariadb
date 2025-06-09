<?php include 'config.php'; // Inclua o config.php primeiro ?>
<?php include 'includes/header.php'; // Em seguida, inclua o header ?>
<?php include 'conexao.php'; ?>
<canvas id="grafico" width="400" height="200"></canvas>
<script src="js/chart.min.js"></script>
<script>
fetch("dados-json.php")
  .then(res => res.json())
  .then(data => {
    new Chart(document.getElementById("grafico"), {
      type: 'bar',
      data: {
        labels: data.labels,
        datasets: [{
          label: 'Nível da Água',
          data: data.valores,
          backgroundColor: 'rgba(54, 162, 235, 0.7)'
        }]
      }
    });
  });
</script>
<?php include 'includes/footer.php'; ?>
