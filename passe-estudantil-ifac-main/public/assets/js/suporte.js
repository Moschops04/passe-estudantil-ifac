document.querySelectorAll('.faq-question').forEach((botao) => {
  botao.addEventListener('click', () => {
    botao.closest('.faq-item').classList.toggle('open');
  });
});
