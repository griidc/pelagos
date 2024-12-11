const cardClick = () => {
  document.querySelectorAll('.card').forEach((card) => {
    const cardElement = card;
    cardElement.onclick = () => {
      if (window.getSelection().toString() === '') {
        const link = card.querySelector('a');
        if (typeof (link) !== 'undefined' && link != null) {
          link.click();
        }
      }
    };
  });
};

export default { cardClick };

cardClick();
