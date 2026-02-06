import AdminLayout from '../../Layouts/AdminLayout'
import { useForm, router, usePage } from '@inertiajs/react'
import { useState } from 'react'
// Gemini
export default function Index({ users, filters }) {
    const { auth } = usePage().props
    const can = (perm) => auth?.permissions?.includes(perm)

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

    // Memfilter daftar Sub Unit berdasarkan Unit yang dipilih di form
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
            password: '', // Password dikosongkan saat edit sesuai logika controller
            role: u.roles?.[0]?.name || 'user', // Mengambil role pertama dari spatie
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
                            <i className="fa fa-plus"></i> Tambah User
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
                                <th width="50">No</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Unit</th>
                                <th>Sub Unit</th>
                                <th width="150">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            {users.data.map((u, i) => (
                                <tr key={u.id}>
                                    <td>{(users.meta?.from || 1) + i}</td>
                                    <td>{u.name}</td>
                                    <td>{u.email}</td>
                                    <td>
                                        <span className={`label ${u.roles?.[0]?.name === 'admin' ? 'label-danger' : 'label-info'}`}>
                                            {u.roles?.[0]?.name || '-'}
                                        </span>
                                    </td>
                                    <td>{u.unit?.unit || '-'}</td>
                                    <td>{u.sub_unit?.sub_unit || '-'}</td>
                                    <td>
                                        <div className="btn-group">
                                            {can('user.update') && (
                                                <button
                                                    className="btn btn-xs btn-warning"
                                                    onClick={() => openEdit(u)}
                                                    title="Edit User"
                                                >
                                                    <i className="fa fa-edit"></i>
                                                </button>
                                            )}

                                            {can('user.delete') && (
                                                <button
                                                    className="btn btn-xs btn-danger"
                                                    onClick={() => {
                                                        if (confirm('Yakin hapus user ini?')) {
                                                            router.delete(`/user/${u.id}`, {
                                                                preserveScroll: true,
                                                            })
                                                        }
                                                    }}
                                                    title="Hapus User"
                                                >
                                                    <i className="fa fa-trash"></i>
                                                </button>
                                            )}
                                        </div>
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
                        <div className="btn-group">
                            {users.links?.map((link, i) => (
                                <button
                                    key={i}
                                    disabled={!link.url}
                                    className={`btn btn-sm ${link.active ? 'btn-primary' : 'btn-default'}`}
                                    onClick={() => router.get(link.url)}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ))}
                        </div>
                    </div>
                </div>
            </div>

            {/* MODAL FORM */}
            {modal && (
                <>
                    <div className="modal fade in" style={{ display: 'block' }}>
                        <div className="modal-dialog modal-lg">
                            <form className="modal-content" onSubmit={submit}>
                                <div className="modal-header">
                                    <button type="button" className="close" onClick={closeModal}>×</button>
                                    <h4 className="modal-title">{edit ? 'Edit' : 'Tambah'} User</h4>
                                </div>

                                <div className="modal-body">
                                    <div className="form-group">
                                        <label>Nama</label>
                                        <input
                                            className="form-control"
                                            value={data.name}
                                            onChange={e => setData('name', e.target.value)}
                                            placeholder="Masukkan nama lengkap"
                                        />
                                        {errors.name && <div className="text-danger">{errors.name}</div>}
                                    </div>

                                    <div className="form-group">
                                        <label>Email</label>
                                        <input
                                            className="form-control"
                                            type="email"
                                            value={data.email}
                                            onChange={e => setData('email', e.target.value)}
                                            placeholder="contoh@email.com"
                                        />
                                        {errors.email && <div className="text-danger">{errors.email}</div>}
                                    </div>

                                    <div className="form-group">
                                        <label>Password {edit && <small className="text-muted">(Kosongkan jika tidak ingin mengubah password)</small>}</label>
                                        <input
                                            type="password"
                                            className="form-control"
                                            value={data.password}
                                            onChange={e => setData('password', e.target.value)}
                                            placeholder="Min. 6 karakter"
                                        />
                                        {errors.password && <div className="text-danger">{errors.password}</div>}
                                    </div>

                                    <div className="row">
                                        <div className="col-md-4">
                                            <div className="form-group">
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
                                                {errors.role && <div className="text-danger">{errors.role}</div>}
                                            </div>
                                        </div>

                                        <div className="col-md-4">
                                            <div className="form-group">
                                                <label>Unit</label>
                                                <select
                                                    className="form-control"
                                                    value={data.unit_id}
                                                    onChange={e => {
                                                        setData(prev => ({
                                                            ...prev,
                                                            unit_id: e.target.value,
                                                            sub_unit_id: '' // Reset sub_unit jika unit berubah
                                                        }))
                                                    }}
                                                >
                                                    <option value="">-- Pilih Unit --</option>
                                                    {filters.units?.map(u => (
                                                        <option key={u.id} value={u.id}>{u.unit}</option>
                                                    ))}
                                                </select>
                                                {errors.unit_id && <div className="text-danger">{errors.unit_id}</div>}
                                            </div>
                                        </div>

                                        <div className="col-md-4">
                                            <div className="form-group">
                                                <label>Sub Unit</label>
                                                <select
                                                    className="form-control"
                                                    value={data.sub_unit_id}
                                                    onChange={e => setData('sub_unit_id', e.target.value)}
                                                    disabled={!data.unit_id}
                                                >
                                                    <option value="">-- Pilih Sub Unit --</option>
                                                    {filteredSubUnits?.map(s => (
                                                        <option key={s.id} value={s.id}>{s.sub_unit}</option>
                                                    ))}
                                                </select>
                                                {errors.sub_unit_id && <div className="text-danger">{errors.sub_unit_id}</div>}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div className="modal-footer">
                                    <button type="button" className="btn btn-default pull-left" onClick={closeModal}>Batal</button>
                                    <button className="btn btn-primary" disabled={processing}>
                                        {processing ? <i className="fa fa-spinner fa-spin"></i> : <i className="fa fa-save"></i>} Simpan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div className="modal-backdrop fade in"></div>
                </>
            )}
        </AdminLayout>
    )
}