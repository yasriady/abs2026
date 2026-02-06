import AdminLayout from '../../Layouts/AdminLayout'
import { useForm, router } from '@inertiajs/react'
import { useState } from 'react'
import ConfirmDeleteModal from '@/Components/ConfirmDeleteModal'

export default function Index({ devices, filters }) {
    const [modal, setModal] = useState(false)
    const [edit, setEdit] = useState(null)
    const [search, setSearch] = useState(filters.search || '')

    // // DELETE MODAL STATE
    // const [deleteTarget, setDeleteTarget] = useState(null)
    const [deleteDevice, setDeleteDevice] = useState(null)

    const { data, setData, post, put, reset, errors, processing } = useForm({
        device_id: '',
        unit_id: '',
        desc: '',
        enabled: true,
        public_key: '',
        private_key: '',
    })

    function openCreate() {
        reset()
        setEdit(null)
        setModal(true)
    }

    function openEdit(d) {
        setEdit(d)
        setData({
            device_id: d.device_id,
            unit_id: d.unit_id,
            desc: d.desc,
            enabled: d.enabled,
            public_key: d.public_key || '',
            private_key: d.private_key || '',
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
            put(`/device/${edit.id}`, {
                preserveScroll: true,
                onSuccess: closeModal,
            })
        } else {
            post('/device', {
                preserveScroll: true,
                onSuccess: closeModal,
            })
        }
    }

    function doSearch(value) {
        setSearch(value)
        router.get('/device', { search: value }, {
            preserveState: true,
            replace: true,
        })
    }

    return (
        <AdminLayout title="Device">

            <div className="box">
                <div className="box-header">
                    <button className="btn btn-primary" onClick={openCreate}>
                        Tambah Device
                    </button>

                    <div className="pull-right">
                        <input
                            className="form-control"
                            placeholder="Search..."
                            style={{ width: '200px' }}
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
                                <th>Device ID</th>
                                <th>Unit</th>
                                <th>Deskripsi</th>
                                <th>Status</th>
                                <th width="120">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            {devices.data.map((d, i) => (
                                <tr key={d.id}>
                                    <td>{(devices.meta?.from || 1) + i}</td>
                                    <td>{d.device_id}</td>
                                    <td>{d.unit_id}</td>
                                    <td>{d.desc}</td>
                                    <td>
                                        {d.enabled
                                            ? <span className="label label-success">Aktif</span>
                                            : <span className="label label-danger">Nonaktif</span>
                                        }
                                    </td>
                                    <td>
                                        <button
                                            className="btn btn-xs btn-warning"
                                            onClick={() => openEdit(d)}
                                        >
                                            Edit
                                        </button>
                                        <button
                                            className="btn btn-xs btn-danger"
                                            onClick={() => setDeleteDevice(d)}
                                        >
                                            Hapus
                                        </button>

                                    </td>
                                </tr>
                            ))}

                            {devices.data.length === 0 && (
                                <tr>
                                    <td colSpan="6" className="text-center text-muted">
                                        Data tidak ditemukan
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>

                    <div className="text-center">
                        {devices.links.map((link, i) => (
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

            {/* MODAL FORM */}
            {modal && (
                <>
                    <div className="modal fade in" style={{ display: 'block' }}>
                        <div className="modal-dialog">
                            <form className="modal-content" onSubmit={submit}>
                                <div className="modal-header">
                                    <button type="button" className="close" onClick={closeModal}>
                                        ×
                                    </button>
                                    <h4>{edit ? 'Edit' : 'Tambah'} Device</h4>
                                </div>

                                <div className="modal-body">
                                    <input
                                        className="form-control"
                                        placeholder="Device ID"
                                        value={data.device_id}
                                        onChange={e => setData('device_id', e.target.value)}
                                    />
                                    {errors.device_id && (
                                        <div className="text-danger">{errors.device_id}</div>
                                    )}

                                    <input
                                        className="form-control"
                                        placeholder="Unit ID"
                                        value={data.unit_id}
                                        onChange={e => setData('unit_id', e.target.value)}
                                    />

                                    <input
                                        className="form-control"
                                        placeholder="Deskripsi"
                                        value={data.desc}
                                        onChange={e => setData('desc', e.target.value)}
                                    />

                                    <label>
                                        <input
                                            type="checkbox"
                                            checked={data.enabled}
                                            onChange={e => setData('enabled', e.target.checked)}
                                        /> Aktif
                                    </label>

                                    <textarea
                                        className="form-control"
                                        placeholder="Public Key"
                                        value={data.public_key}
                                        onChange={e => setData('public_key', e.target.value)}
                                    />

                                    <textarea
                                        className="form-control"
                                        placeholder="Private Key"
                                        value={data.private_key}
                                        onChange={e => setData('private_key', e.target.value)}
                                    />
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

            <ConfirmDeleteModal
                show={!!deleteDevice}
                title="Konfirmasi Hapus Device"
                message={
                    <p>
                        Yakin menghapus device:
                        <strong> {deleteDevice?.nama_device || deleteDevice?.name}</strong>?
                    </p>
                }
                onCancel={() => setDeleteDevice(null)}
                onConfirm={() => {
                    router.delete(`/device/${deleteDevice.id}`, {
                        preserveScroll: true,
                        onSuccess: () => setDeleteDevice(null),
                    })
                }}
            />


        </AdminLayout>
    )
}
