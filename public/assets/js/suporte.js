// Página de Suporte

document.querySelectorAll('.faq-question').forEach((botao) => {
  botao.addEventListener('click', () => {
    botao.closest('.faq-item').classList.toggle('open');
  });
});

const btnConsultar = document.getElementById('btn-consultar-protocolo');
const inputProtocolo = document.getElementById('protocolo-input');
const resultado = document.getElementById('status-resultado');

async function consultarProtocolo() {
  const protocolo = inputProtocolo.value.trim();
  if (!protocolo) return;

  resultado.style.display = 'none';

  try {
    const resposta = await fetch('api/status.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ protocolo }),
    });
    const dados = await resposta.json();

    if (!resposta.ok) {
      throw new Error(dados.erro || 'Não foi possível consultar esse protocolo.');
    }

    resultado.className = 'status-result ok';
    resultado.textContent = dados.mensagem;
    resultado.style.display = 'block';
  } catch (erro) {
    resultado.className = 'status-result erro';
    resultado.textContent = erro.message;
    resultado.style.display = 'block';
  }
}

btnConsultar.addEventListener('click', consultarProtocolo);
inputProtocolo.addEventListener('keydown', (e) => {
  if (e.key === 'Enter') consultarProtocolo();
});
