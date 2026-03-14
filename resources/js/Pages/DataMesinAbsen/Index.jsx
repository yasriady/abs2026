import React from "react";
import { router, usePage } from "@inertiajs/react";
import AdminLayout from "@/Layouts/AdminLayout";

export default function Index() {
    const { data, filters, devices } = usePage().props;

    const handleFilter = (e) => {
        e.preventDefault();
        const form = new FormData(e.target);

        router.get("/data-mesin-absen", Object.fromEntries(form), {
            preserveState: true,
            replace: true
        });
    };

    const resetFilter = () => {
        router.get("/data-mesin-absen");
    };

    return (
        <AdminLayout title="Data Mesin Absen">
            <div className="content-wrapper p-3">

                {/* HEADER */}
                <section className="content-header mb-3">
                    <h1>
                        Data Mesin Absen
                        <small className="ml-2 text-muted">Log Mentah Mesin</small>
                    </h1>
                </section>

                {/* FILTER BOX */}
                <div className="card card-primary card-outline">
                    <div className="card-header">
                        <h3 className="card-title">Filter Data</h3>
                    </div>

                    <form onSubmit={handleFilter}>
                        <div className="card-body row">

                            <div className="col-md-2">
                                <label>Tanggal</label>
                                <input
                                    type="date"
                                    name="tanggal"
                                    defaultValue={filters.tanggal || ""}
                                    className="form-control"
                                />
                            </div>

                            <div className="col-md-2">
                                <label>NIK</label>
                                <input
                                    type="text"
                                    name="nik"
                                    defaultValue={filters.nik || ""}
                                    className="form-control"
                                    placeholder="Cari NIK"
                                />
                            </div>

                            <div className="col-md-2">
                                <label>Nama</label>
                                <input
                                    type="text"
                                    name="nama"
                                    defaultValue={filters.nama || ""}
                                    className="form-control"
                                    placeholder="Cari Nama"
                                />
                            </div>

                            <div className="col-md-2">
                                <label>Device</label>
                                <select
                                    name="device"
                                    defaultValue={filters.device || ""}
                                    className="form-control"
                                >
                                    <option value="">Semua</option>
                                    {devices.map((d, i) => (
                                        <option key={i} value={d}>
                                            {d}
                                        </option>
                                    ))}
                                </select>
                            </div>

                            <div className="col-md-2">
                                <label>Status</label>
                                <select
                                    name="status"
                                    defaultValue={filters.status || ""}
                                    className="form-control"
                                >
                                    <option value="">Semua</option>
                                    <option value="0">Check In</option>
                                    <option value="1">Check Out</option>
                                    <option value="2">Scan</option>
                                </select>
                            </div>

                            <div className="col-md-2 d-flex align-items-end">
                                <button className="btn btn-primary mr-2 w-100">
                                    Filter
                                </button>

                                <button
                                    type="button"
                                    onClick={resetFilter}
                                    className="btn btn-secondary w-100"
                                >
                                    Reset
                                </button>
                            </div>

                        </div>
                    </form>
                </div>

                {/* TABLE */}
                <div className="card">
                    <div className="card-header">
                        <h3 className="card-title">Daftar Log Mesin</h3>
                    </div>

                    <div className="card-body table-responsive p-0">
                        <table className="table table-hover table-bordered text-nowrap">
                            <thead className="thead-light">
                                <tr>
                                    <th>No</th>
                                    <th>NIK</th>
                                    <th>Nama</th>
                                    <th>Tanggal</th>
                                    <th>Jam</th>
                                    <th>Timestamp</th>
                                    <th>Device</th>
                                    <th>Status</th>
                                    <th>Verify</th>
                                </tr>
                            </thead>

                            <tbody>
                                {data.data.length === 0 && (
                                    <tr>
                                        <td colSpan="9" className="text-center text-muted">
                                            Tidak ada data
                                        </td>
                                    </tr>
                                )}

                                {data.data.map((row, index) => (
                                    <tr key={row.id}>
                                        <td>{data.from + index}</td>
                                        <td>{row.nik}</td>
                                        <td>{row.name}</td>
                                        <td>{row.date}</td>
                                        <td>{row.time}</td>
                                        <td>{row.ts}</td>
                                        <td>{row.device_id}</td>
                                        <td>
                                            <span className="badge badge-info">
                                                {row.status}
                                            </span>
                                        </td>
                                        <td>{row.verify}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    {/* PAGINATION */}
                    <div className="card-footer clearfix">
                        <ul className="pagination pagination-sm m-0 float-right">

                            {data.links.map((link, i) => (
                                <li
                                    key={i}
                                    className={`page-item ${link.active ? "active" : ""} ${!link.url ? "disabled" : ""}`}
                                >
                                    <button
                                        className="page-link"
                                        onClick={() => link.url && router.visit(link.url)}
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                    />
                                </li>
                            ))}

                        </ul>
                    </div>
                </div>

            </div>
        </AdminLayout>
    );
}