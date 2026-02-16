import { useState } from "react";
import { Head, router } from "@inertiajs/react";
import AdminLayout from "@/Layouts/AdminLayout";

export default function AbsensiHarian({
    pegawais,
    units = [],
    subUnits = [],
    filters = {},
    stats = {},
}) {

    const fotoUrl = (id) => id ? `/pegawai/foto/${id}` : '/images/no-image.png'
    const absensiFotoIn = (sum_id) => sum_id ? `/absensi/foto/in/${sum_id}` : '/images/no-tap.png'
    const absensiFotoOut = (sum_id) => sum_id ? `/absensi/foto/out/${sum_id}` : '/images/no-tap.png'

    const [unitId, setUnitId] = useState(filters.unit_id || "");
    const [subUnitId, setSubUnitId] = useState(filters.sub_unit_id || "");
    const [date, setDate] = useState(
        filters.date || new Date().toISOString().split("T")[0]
    );
    const [search, setSearch] = useState(filters.search || "");

    const handleFilter = () => {
        router.get(
            "/absensi-harian",
            {
                unit_id: unitId,
                sub_unit_id: subUnitId,
                date: date,
                search: search,
            },
            { preserveState: true }
        );
    };

    return (
        <AdminLayout title="Absensi Harian">
            <Head title="Absensi Harian" />

            <div className="box box-primary">
                {/* ================= FILTER ================= */}
                <div className="box-header with-border">
                    <div className="row">
                        <div className="col-md-3">
                            <label>Unit</label>
                            <select
                                className="form-control"
                                value={unitId}
                                onChange={(e) => {
                                    const value = e.target.value;
                                    setUnitId(value);
                                    setSubUnitId(""); // reset sub unit

                                    router.get(
                                        "/absensi-harian",
                                        {
                                            unit_id: value,
                                            date: date,
                                            search: search,
                                        },
                                        { preserveState: true, replace: true }
                                    );
                                }}
                            >
                                <option value="">-</option>
                                {units.map((u) => (
                                    <option key={u.id} value={u.id}>
                                        {u.unit}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div className="col-md-3">
                            <label>Sub</label>
                            <select
                                className="form-control"
                                value={subUnitId}
                                onChange={(e) =>
                                    setSubUnitId(e.target.value)
                                }
                            >
                                <option value="">-</option>
                                {subUnits.map((s) => (
                                    <option key={s.id} value={s.id}>
                                        {s.sub_unit}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div className="col-md-3">
                            <label>Tgl</label>
                            <input
                                type="date"
                                className="form-control"
                                value={date}
                                onChange={(e) =>
                                    setDate(e.target.value)
                                }
                            />
                        </div>

                        <div className="col-md-3">
                            <label>&nbsp;</label>
                            <button
                                className="btn btn-primary btn-block"
                                onClick={handleFilter}
                            >
                                Show
                            </button>
                        </div>
                    </div>
                </div>

                {/* ================= TABLE ================= */}
                <div className="box-body">

                    {/* Top Bar */}
                    <div className="row" style={{ marginBottom: 10 }}>
                        <div className="col-sm-6">
                            <strong>
                                Menampilkan {stats.from || 0} - {stats.to || 0}
                                {" "} dari {stats.total || 0} data
                            </strong>
                        </div>

                        <div className="col-sm-6 text-right">
                            Search:
                            <input
                                type="text"
                                className="form-control input-sm"
                                style={{
                                    width: 200,
                                    display: "inline-block",
                                    marginLeft: 5,
                                }}
                                value={search}
                                onChange={(e) =>
                                    setSearch(e.target.value)
                                }
                                onKeyDown={(e) => {
                                    if (e.key === "Enter") {
                                        handleFilter();
                                    }
                                }}
                            />
                        </div>
                    </div>

                    <div className="table-responsive">
                        <table className="table table-bordered table-hover" style={{ fontSize: "95%" }}>
                            <thead>
                                <tr>
                                    <th width="25%">IDENTITY</th>
                                    <th width="15%">REF</th>
                                    <th width="15%">MASUK</th>
                                    <th width="15%">PULANG</th>
                                    <th width="30%">KET</th>
                                </tr>
                            </thead>
                            <tbody>
                                {pegawais.data.length === 0 && (
                                    <tr>
                                        <td colSpan="5" className="text-center">
                                            Tidak ada data
                                        </td>
                                    </tr>
                                )}

                                {pegawais.data.map((pegawai) => {
                                    const summary = pegawai.summary;

                                    return (
                                        <tr key={pegawai.id}>
                                            {/* IDENTITY */}
                                            <td>
                                                <strong>{pegawai.nama}</strong>
                                                <br />
                                                <small>NIK: {pegawai.nik}</small>
                                                <br />
                                                <small>NIP: {pegawai.nip}</small>
                                            </td>

                                            {/* REF */}
                                            <td className="text-left">


                                                <a href={fotoUrl(pegawai.id)} data-lightbox="pegawai">
                                                    <img
                                                        src={fotoUrl(pegawai.id)}
                                                        onError={(e) => e.target.src = '/images/no-image.png'}
                                                        style={{ width: 55, height: 65, border: '1px solid #ddd' }}
                                                    />
                                                </a>

                                                <div style={{ fontSize: 12, marginTop: 6 }}>
                                                    {filters.date}
                                                </div>

                                                {summary ? (
                                                    <span
                                                        className={`label ${summary.status_hari_final ===
                                                            "HADIR"
                                                            ? "label-success"
                                                            : "label-danger"
                                                            }`}
                                                    >
                                                        {summary.status_hari_final}
                                                    </span>
                                                ) : (
                                                    <span className="label label-default">
                                                        -
                                                    </span>
                                                )}
                                            </td>

                                            {/* MASUK */}
                                            <td className="text-left">

                                                <a href={absensiFotoIn(summary?.id)} data-lightbox="pegawai">
                                                    <img
                                                        src={absensiFotoIn(summary?.id)}
                                                        onError={(e) => e.target.src = '/images/no-tap.png'}
                                                        style={{ width: 55, height: 65, border: '1px solid #ddd' }}
                                                    />
                                                </a>

                                                <div style={{ fontSize: 14, fontWeight: 600 }}>
                                                    {summary?.time_in_final || "-"}
                                                    {summary?.attr_in && (
                                                        <span style={{ fontSize: 11, color: "#777", marginLeft: 4 }}>
                                                            {summary.attr_in}
                                                        </span>
                                                    )}
                                                </div>

                                                <div>
                                                    <span
                                                        className={`label ${summary?.status_masuk_final ===
                                                            "HADIR"
                                                            ? "label-success"
                                                            : "label-danger"
                                                            }`}
                                                    >
                                                        {summary?.status_masuk_final || "-"}
                                                    </span>
                                                    &nbsp;
                                                    <span>
                                                        <i class="fa fa-map-marker"></i>
                                                        &nbsp;
                                                        {summary?.machine_in && (
                                                            <span style={{ fontSize: 11, color: "#999" }}>
                                                                {summary.machine_in}
                                                            </span>
                                                        )}
                                                    </span>
                                                </div>

                                            </td>

                                            {/* PULANG */}
                                            <td className="text-left">

                                                <a href={absensiFotoOut(summary?.id)} data-lightbox="pegawai">
                                                    <img
                                                        src={absensiFotoOut(summary?.id)}
                                                        onError={(e) => e.target.src = '/images/no-tap.png'}
                                                        style={{ width: 55, height: 65, border: '1px solid #ddd' }}
                                                    />
                                                </a>

                                                <div style={{ fontSize: 14, fontWeight: 600 }}>
                                                    {summary?.time_out_final || "-"}
                                                    {summary?.attr_out && (
                                                        <span style={{ fontSize: 11, color: "#777", marginLeft: 4 }}>
                                                            {summary.attr_out}
                                                        </span>
                                                    )}
                                                </div>

                                                <div>
                                                    <span
                                                        className={`label ${summary?.status_pulang_final ===
                                                            "HADIR"
                                                            ? "label-success"
                                                            : "label-danger"
                                                            }`}
                                                    >
                                                        {summary?.status_pulang_final || "-"}
                                                    </span>
                                                    &nbsp;
                                                    <span>
                                                        <i class="fa fa-map-marker"></i>
                                                        &nbsp;
                                                        {summary?.machine_out && (
                                                            <span style={{ fontSize: 11, color: "#999" }}>
                                                                {summary.machine_out}
                                                            </span>
                                                        )}
                                                    </span>
                                                </div>


                                            </td>

                                            {/* KET */}
                                            <td>
                                                {/* <div>
                                                    {summary?.final_note || "-"}
                                                </div> */}

                                                {summary?.notes_hari && (
                                                    <div>Hari: {summary.notes_hari}</div>
                                                )}

                                                {summary?.notes_in && (
                                                    <div>Pagi: {summary.notes_in}</div>
                                                )}
                                                {summary?.notes_out && (
                                                    <div>Sore: {summary.notes_out}</div>
                                                )}

                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>

                    {/* Pagination */}
                    <div className="text-center">
                        {pegawais.links.map((link, index) => (
                            <button
                                key={index}
                                className={`btn btn-sm ${link.active
                                    ? "btn-primary"
                                    : "btn-default"
                                    }`}
                                disabled={!link.url}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                                onClick={() =>
                                    link.url && router.visit(link.url)
                                }
                                style={{ margin: 2 }}
                            />
                        ))}
                    </div>

                </div>
            </div>
        </AdminLayout>
    );
}
