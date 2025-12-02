import axios from "axios";
import { Notify } from "quasar";

const useOpenStockMovementSource = async (row) => {
  let url = "";
  if (row.ref_type == "sales_order_detail") {
    const response = await axios.get(
      "/web-api/so-id-from-detail-id/" + row.ref_id
    );
    url = route("admin.sales-order.detail", { id: response.data.data });
  } else if (row.ref_type == "sales_order_return_detail") {
    const response = await axios.get(
      "/web-api/sor-id-from-detail-id/" + row.ref_id
    );
    url = route("admin.sales-order-return.detail", { id: response.data.data });
  } else if (row.ref_type == "purchase_order_detail") {
    const response = await axios.get(
      "/web-api/po-id-from-detail-id/" + row.ref_id
    );
    url = route("admin.purchase-order.detail", { id: response.data.data });
  } else if (row.ref_type == "purchase_order_return_detail") {
    const response = await axios.get(
      "/web-api/por-id-from-detail-id/" + row.ref_id
    );
    url = route("admin.purchase-order-return.detail", {
      id: response.data.data,
    });
  } else if (row.ref_type == "stock_adjustment_detail") {
    const response = await axios.get(
      "/web-api/sa-id-from-detail-id/" + row.ref_id
    );
    url = route("admin.stock-adjustment.detail", { id: response.data.data });
  }
  else {
    Notify.create({

      message: 'Tidak ada rincian untuk riwayat ini.',
    });
    return;
  }

  window.open(url, "_blank");
};

export default useOpenStockMovementSource;
