<script type="text/javascript">
  window.addEventListener("keydown", (e) => {
    if (e.ctrlKey && e.key === "Enter") {
      e.preventDefault();
      e.stopPropagation();
      const btn = document.querySelector('#new-order');
      if (btn) btn.click();
      return;
    }

    if (e.ctrlKey && (e.key === "'" || e.key === '"')) {
      window.print();
      return;
    }
  });
</script>
