import { usePage } from "@inertiajs/vue3";
import axios from "axios";
import { Notify, Dialog } from "quasar";
import { nextTick } from "vue";

const _scrollToFirstError = () => {
  const page = usePage();
  const firstErrorKey = Object.keys(page.props.errors)[0];
  if (firstErrorKey) {
    setTimeout(() => {
      const errorElement = document.querySelector('.q-field--error input');
      if (errorElement) {
        errorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
        errorElement.focus();
      }
    }, 0);
  }
};

export function handleSubmit(data) {
  const { form, url } = data;

  form.clearErrors();
  form.post(url,
    {
      preserveScroll: true,
      onSuccess: (response) => {
        // Notify.create({
        //   message: response.message,
        //   icon: "info",
        //   color: "positive",
        //   actions: [
        //     { icon: "close", color: "white", round: true, dense: true },
        //   ],
        // });
      },
      onError: (error) => {
        _scrollToFirstError();
        if (!error || typeof (error.message) !== 'string' || error.message.length === 0)
          return;

        Notify.create({
          message: error.message,
          icon: "info",
          color: "negative",
          actions: [
            { icon: "close", color: "white", round: true, dense: true },
          ],
        });
      },
    }
  );
}

export function handlePost(options) {
  const { message, url, fetchItemsCallback, loading, data } = options;
  Dialog.create({
    title: "Konfirmasi",
    icon: "question",
    message: message,
    focus: "cancel",
    cancel: true,
    persistent: true,
  }).onOk(() => {
    loading.value = true;
    axios
      .post(url, data)
      .then((response) => {
        Notify.create(response.data.message);
        fetchItemsCallback();
      })
      .finally(() => {
        loading.value = false;
      })
      .catch((error) => {
        let message = "";
        if (error.response.data && error.response.data.message) {
          message = error.response.data.message;
        } else if (error.message) {
          message = error.message;
        }

        if (message.length > 0) {
          Notify.create({ message: message, color: "red" });
        }
        console.log(error);
      });
  });
}

// alias
export function handleDelete(data) {
  return handlePost(data);
}

export function handleFetchItems(options) {
  const { pagination, props, rows, url, loading, filter, tableRef } = options;

  let source = props ? props.pagination : pagination.value;

  let params = {
    page: source.page,
    per_page: source.rowsPerPage,
    order_by: source.sortBy,
    order_type: source.descending ? "desc" : "asc",
    filter: filter,
  };

  loading.value = true;

  axios
    .get(url, { params: params })
    .then((response) => {
      rows.value = response.data.data;
      pagination.value.page = response.data.current_page;
      pagination.value.rowsPerPage = response.data.per_page;
      pagination.value.rowsNumber = response.data.total;
      if (props) {
        pagination.value.sortBy = props.pagination.sortBy;
        pagination.value.descending = props.pagination.descending;
      }
    })
    .finally(() => {
      loading.value = false;
      nextTick(() => {
        if (!tableRef || !tableRef.value) return;
        const scrollableElement = tableRef.value.$el.querySelector('.q-table__middle');
        if (!scrollableElement) return;
        scrollableElement.scrollTop = 0;
      });
    });
}
