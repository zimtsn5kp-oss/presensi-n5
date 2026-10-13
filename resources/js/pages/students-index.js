import DataTable from 'datatables.net-dt';
import 'datatables.net-dt/css/dataTables.dataTables.css';

let studentsTable = null;
const studentTableHint = document.getElementById('studentTableHint');
const studentsTableElement = document.getElementById('studentsTable');
const classroomFilter = document.getElementById('classroomFilter');
const showButton = document.getElementById('showStudentData');
const resetButton = document.getElementById('resetStudentFilters');
const isAdmin = studentsTableElement.dataset.admin === '1';

function initStudentsTable() {
    studentTableHint.classList.add('hidden');

    if (studentsTable) {
        studentsTable.ajax.reload();
        return;
    }

    const columns = [
        {data: 'nisn'},
        {data: 'name'},
        {data: 'classroom'},
        {data: 'gender'},
        {data: 'status_badge', orderable: false, searchable: false},
    ];

    if (isAdmin) {
        columns.push({data: 'actions', orderable: false, searchable: false});
    }

    studentsTable = new DataTable(studentsTableElement, {
        processing: true,
        serverSide: true,
        ordering: false,
        ajax: {
            url: studentsTableElement.dataset.url,
            data(data) {
                data.load = 1;
                data.classroom = classroomFilter.value;
            },
        },
        columns,
        language: {
            emptyTable: 'Tidak ada data murid pada filter yang dipilih.',
        },
    });
}

showButton.addEventListener('click', initStudentsTable);
classroomFilter.addEventListener('change', function () {
    if (studentsTable) {
        initStudentsTable();
    }
});
resetButton.addEventListener('click', function () {
    classroomFilter.value = '';
    if (studentsTable) {
        studentsTable.destroy();
        studentsTable = null;
        studentsTableElement.querySelector('tbody').innerHTML = '';
    }
    studentTableHint.classList.remove('hidden');
});
