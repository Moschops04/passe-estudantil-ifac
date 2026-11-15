const form = document.getElementById('form-emissao');
const botao = document.getElementById('btn-emitir');
const senhaInput = document.getElementById('senha');
const matriculaInput = document.getElementById('matricula');

function mostrarErro(mensagem) {
  document.getElementById('alerta-erro-texto').textContent = mensagem;
  document.getElementById('alerta-erro').style.display = 'flex';
}

form.addEventListener('submit', async (evento) => {
  evento.preventDefault();
  document.getElementById('alerta-erro').style.display = 'none';
  document.getElementById('emissao-status').textContent = 'Autenticando no SUAP e gerando a declaração…';
  botao.disabled = true;

  try {
    const resposta = await fetch('api/emitir.php', {
      method: 'POST',
      credentials: 'same-origin',
      cache: 'no-store',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/pdf, application/json' },
      body: JSON.stringify({
        matricula: matriculaInput.value.trim(),
        senha: senhaInput.value,
      }),
    });

    if (!resposta.ok) {
      const erro = await resposta.json().catch(() => ({}));
      throw new Error(erro.erro || 'Não foi possível emitir a declaração. Tente novamente.');
    }

    const pdf = await resposta.blob();
    const url = URL.createObjectURL(pdf);
    const link = document.createElement('a');
    link.href = url;
    link.download = 'declaracao-passe-estudantil.pdf';
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.setTimeout(() => URL.revokeObjectURL(url), 1000);
    document.getElementById('emissao-status').textContent =
      'Declaração emitida. Confira o código de autenticidade no rodapé do PDF.';
  } catch (erro) {
    mostrarErro(erro.message || 'Falha de comunicação. Tente novamente.');
    document.getElementById('emissao-status').textContent = '';
  } finally {
    senhaInput.value = '';
    matriculaInput.value = '';
    botao.disabled = false;
  }
});
