import AdminLayout from '../../Layouts/AdminLayout'
import { useForm, router } from '@inertiajs/react'
import { useState } from 'react'
import ConfirmDeleteModal from '@/Components/ConfirmDeleteModal'

export default function Index({ liburs, filters }) {
    const [modal, setModal] = useState(false)
    const [edit, setEdit] = useState(null)
    const [search, setSearch] = useState(filters.search || '')

    // // DELETE MODAL STATE
    // const [deleteTarget, setDeleteTarget] = useState(null)
    const [deleteLibur, setDeleteLibur] = useState(null)

    const { data, setData, post, put, reset, errors, processing } = useForm({
        date: '',
        year: '',
        category: '',
        description: '',
    })

    function openCreate() {
        reset()
        setEdit(null)
        setModal(true)
    }

    function openEdit(d) {
        setEdit(d)
        setData({
            date: d.date,
            year: d.year,
            category: d.category,
            description: d.description,
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
            put(`/libur/${edit.id}`, {
                preserveScroll: true,
                onSuccess: closeModal,
            })
        } else {
            post('/libur', {
                preserveScroll: true,
                onSuccess: closeModal,
            })
        }
    }

    function doSearch(value) {
        setSearch(value)
        router.get('/libur', { search: value }, {
            preserveState: true,
            replace: true,
        })
    }

    // DELETE FLOW
    function openDelete(d) {
        setDeleteTarget(d)
    }

    function closeDelete() {
        setDeleteTarget(null)
    }

    function confirmDelete() {
        if (!deleteTarget) return

        router.delete(`/libur/${deleteTarget.id}`, {
            preserveScroll: true,
            onSuccess: closeDelete,
        })
    }

    return (
        <AdminLayout title="Hari Libur Nasional">

            <div className="box">
                <div className="box-header">
                    <button className="btn btn-primary" onClick={openCreate}>
                        Tambah Libur
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
                                <th>Tanggal</th>
                                <th>Tahun</th>
                                <th>Kategori</th>
                                <th>Deskripsi</th>
                                <th width="120">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            {liburs.data.map((d, i) => (
                                <tr key={d.id}>
                                    <td>{(liburs.meta?.from || 1) + i}</td>
                                    <td>{d.date}</td>
                                    <td>{d.year}</td>
                                    <td>{d.category}</td>
                                    <td>{d.description}</td>
                                    <td>
                                        <button
                                            className="btn btn-xs btn-warning"
                                            onClick={() => openEdit(d)}
                                        >
                                            Edit
                                        </button>
                                        <button
                                            className="btn btn-xs btn-danger"
                                            onClick={() => setDeleteLibur(d)}
                                        >
                                            Hapus
                                        </button>

                                    </td>
                                </tr>
                            ))}

                            {liburs.data.length === 0 && (
                                <tr>
                                    <td colSpan="6" className="text-center text-muted">
                                        Data tidak ditemukan
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>

                    <div className="text-center">
                        {liburs.links.map((link, i) => (
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
                                    <h4>{edit ? 'Edit' : 'Tambah'} Hari Libur</h4>
                                </div>

                                <div className="modal-body">
                                    <input
                                        type="date"
                                        className="form-control"
                                        value={data.date}
                                        onChange={e => setData('date', e.target.value)}
                                    />
                                    {errors.date && (
                                        <div className="text-danger">{errors.date}</div>
                                    )}

                                    <input
                                        className="form-control"
                                        placeholder="Tahun"
                                        value={data.year}
                                        onChange={e => setData('year', e.target.value)}
                                    />

                                    <input
                                        className="form-control"
                                        placeholder="Kategori"
                                        value={data.category}
                                        onChange={e => setData('category', e.target.value)}
                                    />

                                    <textarea
                                        className="form-control"
                                        placeholder="Deskripsi"
                                        value={data.description}
                                        onChange={e => setData('description', e.target.value)}
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
                show={!!deleteLibur}
                title="Konfirmasi Hapus Hari Libur"
                message={
                    <p>
                        Yakin menghapus hari libur:
                        <strong> {deleteLibur?.nama_libur}</strong>
                        <br />
                        <small className="text-muted">
                            ({deleteLibur?.tanggal})
                        </small>
                    </p>
                }
                onCancel={() => setDeleteLibur(null)}
                onConfirm={() => {
                    router.delete(`/hari-libur-nasional/${deleteLibur.id}`, {
                        preserveScroll: true,
                        onSuccess: () => setDeleteLibur(null),
                    })
                }}
            />


        </AdminLayout>
    )
}
