import AdminLayout from '../../Layouts/AdminLayout'
import { useForm, router, usePage } from '@inertiajs/react'
import { useState } from 'react'
import can from '@/utils/can'
import ConfirmDeleteModal from '@/Components/ConfirmDeleteModal'
// ChatGPT
export default function Index({ users, filters }) {

    const [confirmDelete_x, setConfirmDelete] = useState(null)
    const [deleteUser, setDeleteUser] = useState(null)

    const { auth } = usePage().props
    // const can = (perm) => auth?.permissions?.includes(perm)
    // const can = (perm) => auth?.user?.permissions?.includes(perm) // pindah ke utils/can.js

    const [modal, setModal] = useState(false)
    const [edit, setEdit] = useState(null)
    const [search, setSearch] = useState(filters.search || '')

    const { data, setData, post, put, reset, errors, processing } = useForm({
        name: '',
        email: '',
        password: '',
        role: 'user',
        unit_id: '',
        sub_unit_id: '',
    })

    const filteredSubUnits = filters.sub_units?.filter(
        s => !data.unit_id || String(s.unit_id) === String(data.unit_id)
    )

    function openCreate() {
        reset()
        setEdit(null)
        setModal(true)
    }

    function openEdit(u) {
        setEdit(u)
        setData({
            name: u.name,
            email: u.email,
            password: '',
            role: u.roles?.[0]?.name || 'user',
            unit_id: u.unit_id || '',
            sub_unit_id: u.sub_unit_id || '',
        })
        setModal(true)
    }

    function closeModal() {
        reset()
        setEdit(null)
        setModal(false)
    }

    function submit(e) {
        e.preventDefault()

        if (edit) {
            put(`/user/${edit.id}`, {
                preserveScroll: true,
                onSuccess: closeModal,
            })
        } else {
            post('/user', {
                preserveScroll: true,
                onSuccess: closeModal,
            })
        }
    }

    function doSearch(value) {
        setSearch(value)
        router.get('/user', { search: value }, {
            preserveState: true,
            replace: true,
        })
    }

    return (
        <AdminLayout title="User Management">

            <div className="box">
                <div className="box-header">
                    {can('user.create') && (
                        <button className="btn btn-primary" onClick={openCreate}>
                            Tambah User
                        </button>
                    )}

                    <div className="pull-right">
                        <input
                            className="form-control"
                            placeholder="Search nama / email..."
                            style={{ width: '250px' }}
                            value={search}
                            onChange={e => doSearch(e.target.value)}
                        />
                    </div>
                </div>

                <div className="box-body table-responsive">
                    <table className="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Unit</th>
                                <th>Sub Unit</th>
                                <th width="120">Aksii</th>
                            </tr>
                        </thead>
                        <tbody>
                            {users.data.map((u, i) => (
                                <tr key={u.id}>
                                    <td>{(users.meta?.from || 1) + i}</td>
                                    <td>{u.name}</td>
                                    <td>{u.email}</td>
                                    <td>
                                        <span className="label label-info">
                                            {u.roles?.[0]?.name || '-'}
                                        </span>
                                    </td>
                                    <td>{u.unit?.unit || '-'}</td>
                                    <td>{u.sub_unit?.sub_unit || '-'}</td>
                                    <td>
                                        {can('user.update') && (
                                            <button
                                                className="btn btn-xs btn-warning"
                                                onClick={() => openEdit(u)}
                                            >
                                                Edit
                                            </button>
                                        )}

                                        {can('user.delete') && (
                                            <button
                                                className="btn btn-xs btn-danger"
                                                onClick={() => setDeleteUser(u)}
                                            >
                                                Hapus
                                            </button>

                                        )}
                                    </td>


                                </tr>
                            ))}

                            {users.data.length === 0 && (
                                <tr>
                                    <td colSpan="7" className="text-center text-muted">
                                        Data tidak ditemukan
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>

                    <div className="text-center">
                        {users.links?.map((link, i) => (
                            <button
                                key={i}
                                disabled={!link.url}
                                className={`btn btn-xs ${link.active ? 'btn-primary' : 'btn-default'}`}
                                onClick={() => router.get(link.url)}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                            />
                        ))}
                    </div>
                </div>
            </div>

            {/* MODAL */}
            {modal && (
                <>
                    <div className="modal fade in" style={{ display: 'block' }}>
                        <div className="modal-dialog modal-lg">
                            <form className="modal-content" onSubmit={submit}>
                                <div className="modal-header">
                                    <button type="button" className="close" onClick={closeModal}>
                                        ×
                                    </button>
                                    <h4>{edit ? 'Edit' : 'Tambah'} User</h4>
                                </div>

                                <div className="modal-body">

                                    <label>Nama</label>
                                    <input
                                        className="form-control"
                                        value={data.name}
                                        onChange={e => setData('name', e.target.value)}
                                    />
                                    {errors.name && <div className="text-danger">{errors.name}</div>}

                                    <label>Email</label>
                                    <input
                                        className="form-control"
                                        value={data.email}
                                        onChange={e => setData('email', e.target.value)}
                                    />
                                    {errors.email && <div className="text-danger">{errors.email}</div>}

                                    <label>Password {edit && <small>(Kosongkan jika tidak diubah)</small>}</label>
                                    <input
                                        type="password"
                                        className="form-control"
                                        value={data.password}
                                        onChange={e => setData('password', e.target.value)}
                                    />
                                    {errors.password && <div className="text-danger">{errors.password}</div>}

                                    <div className="row">
                                        <div className="col-md-4">
                                            <label>Role</label>
                                            <select
                                                className="form-control"
                                                value={data.role}
                                                onChange={e => setData('role', e.target.value)}
                                            >
                                                <option value="admin">Admin</option>
                                                <option value="admin_unit">Admin Unit</option>
                                                <option value="admin_subunit">Admin SubUnit</option>
                                                <option value="user">User</option>
                                            </select>
                                        </div>

                                        <div className="col-md-4">
                                            <label>Unit</label>
                                            <select
                                                className="form-control"
                                                value={data.unit_id}
                                                onChange={e => setData('unit_id', e.target.value)}
                                            >
                                                <option value="">-</option>
                                                {filters.units?.map(u => (
                                                    <option key={u.id} value={u.id}>
                                                        {u.unit}
                                                    </option>
                                                ))}
                                            </select>
                                        </div>

                                        <div className="col-md-4">
                                            <label>Sub Unit</label>
                                            <select
                                                className="form-control"
                                                value={data.sub_unit_id}
                                                onChange={e => setData('sub_unit_id', e.target.value)}
                                            >
                                                <option value="">-</option>
                                                {filteredSubUnits?.map(s => (
                                                    <option key={s.id} value={s.id}>
                                                        {s.sub_unit}
                                                    </option>
                                                ))}
                                            </select>
                                        </div>
                                    </div>

                                </div>

                                <div className="modal-footer">
                                    <button
                                        type="button"
                                        className="btn btn-default"
                                        onClick={closeModal}
                                    >
                                        Batal
                                    </button>
                                    <button
                                        className="btn btn-primary"
                                        disabled={processing}
                                    >
                                        {processing ? 'Menyimpan...' : 'Simpan'}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div className="modal-backdrop fade in"></div>
                </>
            )}

            {confirmDelete_x && (
                <>
                    <div className="modal fade in" style={{ display: 'block' }}>
                        <div className="modal-dialog modal-sm">
                            <div className="modal-content">

                                <div className="modal-header bg-red">
                                    <button
                                        type="button"
                                        className="close"
                                        onClick={() => setConfirmDelete(null)}
                                    >
                                        ×
                                    </button>
                                    <h4 className="modal-title">
                                        Konfirmasi Hapus
                                    </h4>
                                </div>

                                <div className="modal-body">
                                    <p>
                                        Yakin hapus user:
                                        <strong> {confirmDelete_x.name}</strong> ?
                                    </p>
                                </div>

                                <div className="modal-footer">
                                    <button
                                        className="btn btn-default"
                                        onClick={() => setConfirmDelete(null)}
                                    >
                                        Batal
                                    </button>
                                    <button
                                        className="btn btn-danger"
                                        onClick={() => {
                                            router.delete(`/user/${confirmDelete_x.id}`, {
                                                preserveScroll: true,
                                                onSuccess: () => setConfirmDelete(null),
                                            })
                                        }}
                                    >
                                        Hapus
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div className="modal-backdrop fade in"></div>
                </>
            )}

            <ConfirmDeleteModal
                show={!!deleteUser}
                message={
                    <p>
                        Yakin hapus user:
                        <strong> {deleteUser?.name}</strong> ?
                    </p>
                }
                onCancel={() => setDeleteUser(null)}
                onConfirm={() => {
                    router.delete(`/user/${deleteUser.id}`, {
                        preserveScroll: true,
                        onSuccess: () => setDeleteUser(null),
                    })
                }}
            />

        </AdminLayout>
    )
}
