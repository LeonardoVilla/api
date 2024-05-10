document.getElementById('activityForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Evita o comportamento padrão do formulário

    // Obtém os valores dos campos
    var nome = document.getElementById('nome').value;
    var duvida = document.getElementById('duvida').value;
    var natividade = document.getElementById('natividade').value;
    var arquivo = document.getElementById('arquivo').files[0];

    // Cria um objeto FormData para enviar os dados do formulário
    var formData = new FormData();
    formData.append('nome', nome);
    formData.append('duvida', duvida);
    formData.append('natividade', natividade);
    formData.append('arquivo', arquivo);

    // Envia a requisição POST para a API
    fetch('https://leonardosistema.000webhostapp.com/api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erro ao enviar os dados.');
        }
        return response.json();
    })
    .then(data => {
        // Se a resposta da API contém uma mensagem de sucesso, exibe um alerta
        if (data && data.message === "Atividade criada com sucesso.") {
            alert(data.message);
            var modal = document.getElementById('Modal'); // Substitua 'Modal' pelo ID do seu modal
            var modalBootstrap = new bootstrap.Modal(modal);
            modalBootstrap.show();
            // Limpa os valores dos campos do formulário
            document.getElementById('nome').value = '';
            document.getElementById('duvida').value = '';
            document.getElementById('natividade').value = '';
            document.getElementById('arquivo').value = '';
        } else {
            throw new Error('Erro ao cadastrar atividade.');
        }
    })
    .catch(error => {
        alert(error.message);
    });
});
