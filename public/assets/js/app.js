// Portal do Estudante — Declaração de Passe Estudantil
// Este arquivo troca as funções de simulação do protótipo por chamadas
// reais aos endpoints em public/api/*.php.

let timerInterval;
let emailAtual = '';

const el = (id) => document.getElementById(id);

async function chamarApi(caminho, corpo) {
  const resposta = await fetch(`api/${caminho}`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(corpo),
  });
  const dados = await resposta.json();
  if (!resposta.ok) {
    throw new Error(dados.erro || 'Ocorreu um erro. Tente novamente.');
  }
  return dados;
}

function mostrarErro(mensagem) {
  el('alerta-erro-texto').textContent = mensagem;
  el('alerta-erro').style.display = 'flex';
}

function limparErro() {
  el('alerta-erro').style.display = 'none';
}

function iniciais(nome) {
  return nome
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map((p) => p[0].toUpperCase())
    .join('');
}

async function verificarMatricula() {
  limparErro();
  const matricula = el('matricula').value.trim();
  if (!matricula) return;

  const botao = el('btn-verificar');
  botao.disabled = true;

  try {
    const aluno = await chamarApi('verificar.php', { matricula });

    el('aluno-info').style.display = 'block';
    el('aluno-initials').textContent = iniciais(aluno.nome);
    el('aluno-nome').textContent = aluno.nome;
    el('aluno-curso').textContent = `${aluno.curso} · ${aluno.semestre}º semestre`;
    el('email').value = aluno.email_institucional;

    el('btn-enviar-confirmacao').disabled = false;
  } catch (erro) {
    el('aluno-info').style.display = 'none';
    el('btn-enviar-confirmacao').disabled = true;
    mostrarErro(erro.message);
  } finally {
    botao.disabled = false;
  }
}

async function enviarConfirmacao() {
  limparErro();
  const email = el('email').value;

  try {
    const dados = await chamarApi('solicitar.php', { email });

    emailAtual = email;
    el('email-display').textContent = dados.email_mascarado;

    if (dados.token_dev) {
      el('codigo-dev-valor').textContent = dados.token_dev;
      el('aviso-dev').style.display = 'flex';
    } else {
      el('aviso-dev').style.display = 'none';
    }

    setStep(2);
    startTimer(dados.expira_em_segundos);
  } catch (erro) {
    mostrarErro(erro.message);
  }
}

async function confirmarToken() {
  limparErro();
  const token = el('codigo-input').value.trim();
  if (token.length < 4) return;

  try {
    const dados = await chamarApi('confirmar.php', { token });
    clearInterval(timerInterval);
    el('protocol-num').textContent = dados.protocolo;
    setStep(3);
  } catch (erro) {
    mostrarErro(erro.message);
  }
}

function setStep(n) {
  [1, 2, 3].forEach((i) => {
    el('screen-step' + i).style.display = i === n ? 'block' : 'none';
    const ind = el('step' + i + '-ind');
    ind.className = 'step' + (i < n ? ' done' : i === n ? ' active' : '');
  });
}

function startTimer(segundosIniciais) {
  clearInterval(timerInterval);
  let segundos = segundosIniciais;
  atualizarCountdown(segundos);
  timerInterval = setInterval(() => {
    segundos--;
    if (segundos <= 0) {
      clearInterval(timerInterval);
      mostrarErro('O código expirou. Solicite um novo.');
      return;
    }
    atualizarCountdown(segundos);
  }, 1000);
}

function atualizarCountdown(segundos) {
  const m = Math.floor(segundos / 60);
  const s = segundos % 60;
  el('countdown').textContent = `${m}:${String(s).padStart(2, '0')}`;
}

function novaSolicitacao() {
  el('matricula').value = '';
  el('email').value = '';
  el('codigo-input').value = '';
  el('aluno-info').style.display = 'none';
  el('btn-enviar-confirmacao').disabled = true;
  limparErro();
  setStep(1);
}

// --- ligação dos eventos ---
el('btn-verificar').addEventListener('click', verificarMatricula);
el('matricula').addEventListener('keydown', (e) => {
  if (e.key === 'Enter') verificarMatricula();
});
el('btn-enviar-confirmacao').addEventListener('click', enviarConfirmacao);
el('btn-confirmar-token').addEventListener('click', confirmarToken);
el('codigo-input').addEventListener('keydown', (e) => {
  if (e.key === 'Enter') confirmarToken();
});
el('btn-voltar').addEventListener('click', () => setStep(1));
el('btn-nova-solicitacao').addEventListener('click', novaSolicitacao);
el('link-reenviar').addEventListener('click', (e) => {
  e.preventDefault();
  enviarConfirmacao();
});
