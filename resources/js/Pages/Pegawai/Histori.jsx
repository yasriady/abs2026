import AdminLayout from '@/Layouts/AdminLayout'
import { router, usePage } from '@inertiajs/react'
import { useState } from 'react'

export default function Histori({ pegawai, histories, filters }) {

    const [from, setFrom] = useState(filters?.from || '')
    const [to, setTo] = useState(filters?.to || '')

    const { auth = {} } = usePage().props
    const role = auth?.user?.role

    function submitFilter(e) {
        e.preventDefault()

        router.get(
            `/pegawai/${pegawai.id}/histori`,
            { from, to },
            { preserveState: true, replace: true }
        )
    }

    return (
        <AdminLayout title="Histori Pegawai">

            {/* ================= HEADER ================= */}
            <div className="box">
                <div className="box-body">
                    <h4 style={{ marginTop: 0 }}>
                        {pegawai.nama}
                    </h4>
                    <div className="text-muted">
                        ({pegawai.id}) NIK: {pegawai.nik} | NIP: {pegawai.nip || '-'}
                    </div>
                </div>
            </div>

            {/* ================= FILTER ================= */}
            <div className="box">
                <form className="box-body" onSubmit={submitFilter}>
                    <div className="row">
                        <div className="col-md-3">
                            <label>Dari Tanggal</label>
                            <input
                                type="date"
                                className="form-control"
                                value={from}
                                onChange={(e) => setFrom(e.target.value)}
                            />
                        </div>

                        <div className="col-md-3">
                            <label>Sampai Tanggal</label>
                            <input
                                type="date"
                                className="form-control"
                                value={to}
                                onChange={(e) => setTo(e.target.value)}
                            />
                        </div>

                        <div className="col-md-2">
                            <label>&nbsp;</label>
                            <button className="btn btn-primary btn-block">
                                Filter
                            </button>
                        </div>

                        <div className="col-md-2">
                            <label>&nbsp;</label>
                            <button
                                type="button"
                                className="btn btn-default btn-block"
                                onClick={() =>
                                    router.get(`/pegawai/${pegawai.id}/histori`)
                                }
                            >
                                Reset
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {/* ================= TABLE ================= */}
            <div className="box">
                <div className="box-body table-responsive">
                    <table className="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="40">No</th>
                                <th>Status</th>
                                <th>Periode</th>
                                <th>Unit</th>
                                <th>Sub Unit</th>
                                <th>Lokasi</th>
                                <th>Struktur</th>
                                <th>Aktif</th>
                                <th width="140">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            {histories.data.map((h, i) => (
                                <tr key={h.id}>
                                    <td>
                                        {(histories.meta?.from ?? 1) + i}
                                    </td>

                                    <td>
                                        <span className="label label-info">
                                            {(h.status_kepegawaian || '-').toUpperCase()}
                                        </span>
                                    </td>

                                    <td>
                                        {h.begin_date}
                                        {h.end_date && (
                                            <span className="text-muted">
                                                {' '}→ {h.end_date}
                                            </span>
                                        )}
                                    </td>

                                    <td>{h.id_unit || '-'}</td>
                                    <td>{h.id_sub_unit || '-'}</td>
                                    <td>{h.lokasi_kerja || '-'}</td>
                                    <td>{h.id_struktur_organisasi || '-'}</td>

                                    <td>
                                        {h.is_active ? (
                                            <span className="label label-success">
                                                AKTIF
                                            </span>
                                        ) : (
                                            <span className="label label-default">
                                                -
                                            </span>
                                        )}
                                    </td>

                                    <td>
                                        <button
                                            className="btn btn-xs btn-warning"
                                            onClick={() =>
                                                router.get(
                                                    `/pegawai/${pegawai.id}/histori/${h.id}/edit`
                                                )
                                            }
                                        >
                                            <i className="fa fa-pencil"></i>
                                        </button>

                                        {role === 'admin' && (
                                            <button
                                                className="btn btn-xs btn-danger"
                                                style={{ marginLeft: 5 }}
                                                onClick={() =>
                                                    router.get(
                                                        `/pegawai/${pegawai.id}/histori/${h.id}/raw-edit`
                                                    )
                                                }
                                            >
                                                <i className="fa fa-bug"></i>
                                            </button>
                                        )}
                                    </td>
                                </tr>
                            ))}

                            {histories.data.length === 0 && (
                                <tr>
                                    <td colSpan="9" className="text-center text-muted">
                                        Tidak ada histori ditemukan
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                {/* ================= PAGINATION ================= */}
                <div
                    className="box-footer clearfix"
                    style={{
                        display: 'flex',
                        justifyContent: 'space-between',
                        alignItems: 'center'
                    }}
                >
                    {/* BADGE JUMLAH */}
                    <span
                        className="label label-warning"
                        style={{
                            fontSize: 12,
                            padding: '4px 8px',
                            borderRadius: 3
                        }}
                        title={`Menampilkan ${histories.meta?.from || 0}–${histories.meta?.to || 0} dari ${histories.meta?.total || 0}`}
                    >
                        {histories.meta?.from || 0}–{histories.meta?.to || 0} / {histories.meta?.total || 0}
                    </span>

                    {/* TOMBOL PAGINATION */}
                    <div>
                        {histories.links.map((link, i) => (
                            <button
                                key={i}
                                className={`btn btn-xs ${link.active
                                    ? 'btn-primary'
                                    : 'btn-default'
                                    }`}
                                disabled={!link.url}
                                onClick={() =>
                                    router.get(link.url, {}, { preserveState: true })
                                }
                                dangerouslySetInnerHTML={{
                                    __html: link.label
                                }}
                            />
                        ))}
                    </div>
                </div>
            </div>
        </AdminLayout>
    )
}
