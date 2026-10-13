import DataTable from 'datatables.net-dt';
import 'datatables.net-dt/css/dataTables.dataTables.css';

const teachersTableElement = document.getElementById('teachersTable');

new DataTable(teachersTableElement, {
    processing: true,
    serverSide: true,
    ajax: teachersTableElement.dataset.url,
    columns: [
        {data: 'nip'},
        {data: 'name'},
        {data: 'subject'},
        {data: 'status_badge', orderable: false, searchable: false},
        {data: 'actions', orderable: false, searchable: false},
    ],
});
