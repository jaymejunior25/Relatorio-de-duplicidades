$(document).ready(function() {
    // Simular o progresso da barra de carregamento
    let progress = 0;
    let interval = setInterval(function() {
        progress += 10;
        $('#progress-bar').css('width', progress + '%').attr('aria-valuenow', progress);
        if (progress >= 100) {
            clearInterval(interval);
            $('#welcome-message').addClass('d-none');
            $('#content').removeClass('d-none');
            loadResults();
        }
    }, 500);

    function loadResults() {
        // Requisição AJAX para carregar os resultados da consulta
        $.ajax({
            url: 'load_results.php',
            method: 'GET',
            success: function(data) {
                let results = JSON.parse(data);
                $('#total-rows').text(results.totalRows);
                $('#total-duplicates').text(results.totalDuplicates);

                let tableBody = '';
                results.data.forEach(function(row) {
                    tableBody += '<tr>';
                    tableBody += '<td>' + row.cdpesfis + '</td>';
                    tableBody += '<td>' + row.nmpesfis + '</td>';
                    tableBody += '<td>' + row.dhnascto + '</td>';
                    tableBody += '<td>' + row.nmpesfismae + '</td>';
                    tableBody += '<td>' + row.tpdoctoident + '</td>';
                    tableBody += '<td>' + row.nrdoctoident + '</td>';
                    tableBody += '<td>' + row.cdexpedident + '</td>';
                    tableBody += '<td>' + row.dtemissdocto + '</td>';
                    tableBody += '</tr>';
                });
                $('#results-table-body').html(tableBody);
            },
            error: function(xhr, status, error) {
                console.error('Erro ao carregar resultados: ', error);
            }
        });
    }
});
