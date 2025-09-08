import { useQuasar } from "quasar";

const $q = useQuasar();

const notify = (msg, color = null, pos = "bottom", icon = null) => {
  $q.notify({
    message: msg,
    color: color,
    position: pos,
    icon: icon,
  });
};

const showWarning = (msg, pos = "bottom") => {
  notify(msg, "warning", pos);
};

const showError = (msg, pos = "bottom") => {
  notify(msg, "negative", pos);
};

const showInfo = (msg, pos = "bottom") => {
  notify(msg, "grey", pos);
};

export { notify, showWarning, showError, showInfo };
