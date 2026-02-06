import AdminLayout from '@/Layouts/AdminLayout'
import { router } from '@inertiajs/react'
import { useState } from 'react'
import { usePage } from "@inertiajs/react";

export default function Histori({ pegawai, histories, filters }) {

    const [from, setFrom] = useState(filters.from || '')
    const [to, setTo] = useState(filters.to || '')

    const { auth } = usePage().props

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

            {/* ================= TIMELINE ================= */}
            <div className="box">
                <div className="box-body">
                    <ul className="timeline">

                        {histories.data.map((h) => (
                            <li key={h.id}>
                                <i
                                    className={`fa ${h.is_active
                                        ? 'fa-check bg-green'
                                        : 'fa-history bg-gray'
                                        }`}
                                />

                                <div className="timeline-item">
                                    <span className="time">
                                        <i className="fa fa-calendar"></i>{' '}
                                        {h.begin_date}
                                        {h.end_date && ` → ${h.end_date}`}
                                    </span>

                                    <h3 className="timeline-header">
                                        <strong>
                                            {h.status_kepegawaian.toUpperCase()}
                                        </strong>
                                        {h.is_active && (
                                            <span
                                                className="label label-success"
                                                style={{ marginLeft: 8 }}
                                            >
                                                AKTIF
                                            </span>
                                        )}
                                    </h3>

                                    <div className="timeline-body">
                                        <table className="table table-condensed">
                                            <tbody>
                                                <tr>
                                                    <td width="160">
                                                        <strong>Unit</strong>
                                                    </td>
                                                    <td>{h.id_unit || '-'}</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <strong>Sub Unit</strong>
                                                    </td>
                                                    <td>{h.id_sub_unit || '-'}</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <strong>Lokasi</strong>
                                                    </td>
                                                    <td>{h.lokasi_kerja || '-'}</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <strong>Struktur</strong>
                                                    </td>
                                                    <td>
                                                        {h.id_struktur_organisasi || '-'}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div className="timeline-footer">
                                        <button
                                            className="btn btn-xs btn-warning"
                                            onClick={() =>
                                                router.get(
                                                    `/pegawai/${pegawai.id}/histori/${h.id}/edit`
                                                )
                                            }
                                        >
                                            <i className="fa fa-pencil"></i> Edit
                                        </button>


                                        {auth?.user?.role === 'admin' && (
                                            <button
                                                className="btn btn-xs btn-danger"
                                                style={{ marginLeft: 5 }}
                                                onClick={() =>
                                                    router.get(
                                                        `/pegawai/${pegawai.id}/histori/${h.id}/raw-edit`
                                                    )
                                                }
                                            >
                                                <i className="fa fa-bug"></i> RAW Edit
                                            </button>
                                        )}


                                    </div>

                                </div>
                            </li>
                        ))}

                        {histories.data.length === 0 && (
                            <li>
                                <i className="fa fa-info bg-yellow" />
                                <div className="timeline-item">
                                    <h3 className="timeline-header">
                                        Tidak ada histori ditemukan
                                    </h3>
                                </div>
                            </li>
                        )}
                    </ul>
                </div>

                {/* ================= PAGINATION ================= */}
                <div className="box-footer text-center">
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
                                __html: link.label,
                            }}
                        />
                    ))}
                </div>
            </div>
        </AdminLayout>
    )
}
