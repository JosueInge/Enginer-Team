document.addEventListener('DOMContentLoaded', () => {
  const input = document.getElementById('inputBusqueda');
  if (!input) return;
  
  // permite pintar al realizar la busqueda (noticias o denuncias)
  const targets = {
    inicio:      document.getElementById('contenedor-noticias'),
    clima:       document.getElementById('contenedor-noticias'),
    deportes:    document.getElementById('contenedor-noticias'),
    educacion:   document.getElementById('contenedor-noticias'),
    turismo:     document.getElementById('contenedor-noticias'),
    denuncias:   document.getElementById('contenedor-denuncias'),
  };

  let timer;
  const debounceMS = 350;

  input.addEventListener('input', () => {
    clearTimeout(timer);
    timer = setTimeout(() => buscar(input), debounceMS);
  });

  async function buscar(el) {
    const term = el.value.trim();
    const categoria = el.dataset.categoria;
    const contenedor = targets[categoria] || targets['inicio'];
    if (!contenedor) return;

    // Si el campo quedó vacío → recargar “todo” la página (opcional) o pedir sin término.
    const url = `buscar.php?categoria=${encodeURIComponent(categoria)}&term=${encodeURIComponent(term)}`;

    try {
      const r = await fetch(url, { headers: { 'X-Requested-With': 'fetch' }});
      if (!r.ok) throw new Error('error');

      // El back‑end devuelve HTML listo para inyectar
      contenedor.innerHTML = await r.text();
    } catch (err) {
      console.error(err);
    }
  }
});
