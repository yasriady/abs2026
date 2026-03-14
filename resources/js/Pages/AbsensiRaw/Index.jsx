import AdminLayout from '@/Layouts/AdminLayout'
import { router } from '@inertiajs/react'
import { useState } from 'react'

export default function Index({ rows, filters, isFiltered, stats }) {

    const [form, setForm] = useState({
        tanggal: filters?.tanggal || '',
        nik: filters?.nik || '',
        search: filters?.search || ''
    })

    function submitFilter() {
        router.get(route('absensi.raw'), {
            ...form,
            filter: true
        }, {
            preserveState: true,
            replace: true
        })
    }

    function changePage(url) {
        router.visit(url, { preserveState: true })
    }

    return (
        <AdminLayout title="Data Mesin Absen">

            {/* FILTER */}
            <div className="box box-primary">
                <div className="box-body" style={{ display: 'flex', gap: 10 }}>

                    <input type="date"
                        className="form-control"
                        value={form.tanggal}
                        onChange={e => setForm({ ...form, tanggal: e.target.value })}
                    />

                    <input type="text"
                        placeholder="NIK"
                        className="form-control"
                        value={form.nik}
                        onChange={e => setForm({ ...form, nik: e.target.value })}
                    />

                    <input type="text"
                        placeholder="Search"
                        className="form-control"
                        value={form.search}
                        onChange={e => setForm({ ...form, search: e.target.value })}
                    />

                    <button className="btn btn-primary" onClick={submitFilter}>
                        Filter
                    </button>

                </div>
            </div>


            {/* INFO */}
            {!isFiltered && (
                <div className="alert alert-info">
                    Silakan filter data terlebih dahulu
                </div>
            )}

            {stats && (
                <div className="alert alert-default">
                    Menampilkan <b>{stats.from}</b> - <b>{stats.to}</b> dari <b>{stats.total}</b> data
                </div>
            )}


            {/* TABLE */}
            {rows?.data?.length > 0 && (
                <div className="box">
                    <div className="box-body table-responsive">

                        <table className="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>NIK</th>
                                    <th>Nama</th>
                                    <th>Tanggal</th>
                                    <th>Jam</th>
                                    <th>Device</th>
                                </tr>
                            </thead>
                            <tbody>
                                {rows.data.map((row, i) => (
                                    <tr key={row.id}>
                                        <td>{rows.from + i}</td>
                                        <td>{row.nik}</td>
                                        <td>{row.nama}</td>
                                        <td>{row.date}</td>
                                        <td>{row.time}</td>
                                        <td>{row.device}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>

                        {/* PAGINATION */}
                        <div style={{ display: 'flex', gap: 5 }}>
                            {rows.links.map((link, i) => (
                                <button
                                    key={i}
                                    disabled={!link.url}
                                    className={`btn btn-sm ${link.active ? 'btn-primary' : 'btn-default'}`}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                    onClick={() => link.url && changePage(link.url)}
                                />
                            ))}
                        </div>

                    </div>
                </div>
            )}

        </AdminLayout>
    )
}