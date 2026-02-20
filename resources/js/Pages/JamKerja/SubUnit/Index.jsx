import AdminLayout from '@/Layouts/AdminLayout'
import ConfirmDeleteModal from '@/Components/ConfirmDeleteModal'
import { router, useForm } from '@inertiajs/react'
import { useMemo, useState } from 'react'

function toHm(value) {
  if (!value) return ''
  return String(value).slice(0, 5)
}

export default function Index({ jadwals, filters, hariOptions, units, subUnits }) {
  const [modal, setModal] = useState(false)
  const [edit, setEdit] = useState(null)
  const [deleteTarget, setDeleteTarget] = useState(null)

  const [search, setSearch] = useState(filters.search || '')
  const [filterUnitId, setFilterUnitId] = useState(filters.unit_id || '')
  const [filterSubUnitId, setFilterSubUnitId] = useState(filters.sub_unit_id || '')
  const [filterStartDate, setFilterStartDate] = useState(filters.start_date || '')

  const [modalUnitId, setModalUnitId] = useState('')

  const hariMap = useMemo(() => {
    const map = {}
    ;(hariOptions || []).forEach((h) => {
      map[h.value] = h.label
    })
    return map
  }, [hariOptions])

  const filterSubUnitOptions = useMemo(() => {
    if (!filterUnitId) return subUnits || []
    return (subUnits || []).filter((s) => String(s.unit_id) === String(filterUnitId))
  }, [subUnits, filterUnitId])

  const modalSubUnitOptions = useMemo(() => {
    if (!modalUnitId) return []
    return (subUnits || []).filter((s) => String(s.unit_id) === String(modalUnitId))
  }, [subUnits, modalUnitId])

  const { data, setData, post, put, reset, errors, processing } = useForm({
    sub_unit_id: '',
    hari: 1,
    start_date: '',
    end_date: '',
    jam_masuk: '',
    jam_pulang: '',
  })

  function applyFilters(payload = {}) {
    router.get(
      '/jam-kerja/sub-unit',
      {
        search,
        unit_id: filterUnitId,
        sub_unit_id: filterSubUnitId,
        start_date: filterStartDate,
        ...payload,
      },
      { preserveState: true, replace: true }
    )
  }

  function resetFilters() {
    setSearch('')
    setFilterUnitId('')
    setFilterSubUnitId('')
    setFilterStartDate('')
    router.get('/jam-kerja/sub-unit', {}, { preserveState: true, replace: true })
  }

  function openCreate() {
    const firstUnitId = units?.[0]?.id || ''
    const firstSubUnit = (subUnits || []).find(
      (s) => String(s.unit_id) === String(firstUnitId)
    )

    setEdit(null)
    reset()
    setModalUnitId(firstUnitId)
    setData({
      sub_unit_id: firstSubUnit?.id || '',
      hari: 1,
      start_date: '',
      end_date: '',
      jam_masuk: '',
      jam_pulang: '',
    })
    setModal(true)
  }

  function openEdit(item) {
    setEdit(item)
    setModalUnitId(item.parent_unit_id || '')
    setData({
      sub_unit_id: item.sub_unit_id,
      hari: item.hari,
      start_date: item.start_date || '',
      end_date: item.end_date || '',
      jam_masuk: toHm(item.jam_masuk),
      jam_pulang: toHm(item.jam_pulang),
    })
    setModal(true)
  }

  function closeModal() {
    setModal(false)
    setEdit(null)
    setModalUnitId('')
    reset()
  }

  function submit(e) {
    e.preventDefault()

    if (edit) {
      put(`/jam-kerja/sub-unit/${edit.id}`, {
        preserveScroll: true,
        onSuccess: closeModal,
      })
      return
    }

    post('/jam-kerja/sub-unit', {
      preserveScroll: true,
      onSuccess: closeModal,
    })
  }

  return (
    <AdminLayout title="Jadwal Sub Unit">
      <div className="box">
        <div className="box-header">
          <button className="btn btn-primary" onClick={openCreate}>
            Tambah Jadwal Sub Unit
          </button>
        </div>

        <div className="box-body">
          <div className="row">
            <div className="col-sm-3">
              <label>Unit</label>
              <select
                className="form-control"
                value={filterUnitId}
                onChange={(e) => {
                  const value = e.target.value
                  setFilterUnitId(value)
                  setFilterSubUnitId('')
                }}
              >
                <option value="">Semua Unit</option>
                {(units || []).map((u) => (
                  <option key={u.id} value={u.id}>
                    {u.unit}
                  </option>
                ))}
              </select>
            </div>
            <div className="col-sm-3">
              <label>Sub Unit</label>
              <select
                className="form-control"
                value={filterSubUnitId}
                onChange={(e) => setFilterSubUnitId(e.target.value)}
              >
                <option value="">Semua Sub Unit</option>
                {filterSubUnitOptions.map((s) => (
                  <option key={s.id} value={s.id}>
                    {s.sub_unit}
                  </option>
                ))}
              </select>
            </div>
            <div className="col-sm-2">
              <label>Mulai Berlaku</label>
              <input
                type="date"
                className="form-control"
                value={filterStartDate}
                onChange={(e) => setFilterStartDate(e.target.value)}
              />
            </div>
            <div className="col-sm-2">
              <label>Search</label>
              <input
                className="form-control"
                placeholder="Search..."
                value={search}
                onChange={(e) => setSearch(e.target.value)}
              />
            </div>
            <div className="col-sm-2" style={{ paddingTop: '25px' }}>
              <button className="btn btn-primary btn-sm" onClick={() => applyFilters()}>
                Terapkan
              </button>
              <button
                className="btn btn-default btn-sm"
                style={{ marginLeft: '6px' }}
                onClick={resetFilters}
              >
                Reset
              </button>
            </div>
          </div>
        </div>

        <div className="box-body table-responsive">
          <table className="table table-bordered table-striped">
            <thead>
              <tr>
                <th width="50">No</th>
                <th>Unit</th>
                <th>Sub Unit</th>
                <th>Hari</th>
                <th>Mulai Berlaku</th>
                <th>Akhir Berlaku</th>
                <th>Jam Masuk</th>
                <th>Jam Pulang</th>
                <th width="120">Aksi</th>
              </tr>
            </thead>
            <tbody>
              {jadwals.data.map((item, i) => (
                <tr key={item.id}>
                  <td>{(jadwals.from || 1) + i}</td>
                  <td>{item.unit_name || '-'}</td>
                  <td>{item.sub_unit_name || '-'}</td>
                  <td>{hariMap[item.hari] || item.hari}</td>
                  <td>{item.start_date || '-'}</td>
                  <td>{item.end_date || '-'}</td>
                  <td>{toHm(item.jam_masuk)}</td>
                  <td>{toHm(item.jam_pulang)}</td>
                  <td>
                    <button
                      className="btn btn-xs btn-warning"
                      onClick={() => openEdit(item)}
                    >
                      Edit
                    </button>
                    <button
                      className="btn btn-xs btn-danger"
                      onClick={() => setDeleteTarget(item)}
                    >
                      Hapus
                    </button>
                  </td>
                </tr>
              ))}

              {jadwals.data.length === 0 && (
                <tr>
                  <td colSpan="9" className="text-center text-muted">
                    Belum ada data jadwal sub unit
                  </td>
                </tr>
              )}
            </tbody>
          </table>

          <div className="text-center">
            {jadwals.links.map((link, i) => (
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

      {modal && (
        <>
          <div className="modal fade in" style={{ display: 'block' }}>
            <div className="modal-dialog">
              <form className="modal-content" onSubmit={submit}>
                <div className="modal-header">
                  <button type="button" className="close" onClick={closeModal}>
                    x
                  </button>
                  <h4>{edit ? 'Edit' : 'Tambah'} Jadwal Sub Unit</h4>
                </div>

                <div className="modal-body">
                  <div className="form-group">
                    <label>Unit</label>
                    <select
                      className="form-control"
                      value={modalUnitId}
                      onChange={(e) => {
                        const value = e.target.value
                        setModalUnitId(value)
                        const firstSubUnit = (subUnits || []).find(
                          (s) => String(s.unit_id) === String(value)
                        )
                        setData('sub_unit_id', firstSubUnit?.id || '')
                      }}
                    >
                      <option value="">Pilih Unit</option>
                      {(units || []).map((u) => (
                        <option key={u.id} value={u.id}>
                          {u.unit}
                        </option>
                      ))}
                    </select>
                  </div>

                  <div className="form-group">
                    <label>Sub Unit</label>
                    <select
                      className="form-control"
                      value={data.sub_unit_id}
                      onChange={(e) => {
                        const value = e.target.value
                        setData('sub_unit_id', value === '' ? '' : Number(value))
                      }}
                    >
                      <option value="">Pilih Sub Unit</option>
                      {modalSubUnitOptions.map((s) => (
                        <option key={s.id} value={s.id}>
                          {s.sub_unit}
                        </option>
                      ))}
                    </select>
                    {errors.sub_unit_id && (
                      <div className="text-danger">{errors.sub_unit_id}</div>
                    )}
                  </div>

                  <div className="form-group">
                    <label>Hari</label>
                    <select
                      className="form-control"
                      value={data.hari}
                      onChange={(e) => setData('hari', Number(e.target.value))}
                    >
                      {hariOptions.map((h) => (
                        <option key={h.value} value={h.value}>
                          {h.label}
                        </option>
                      ))}
                    </select>
                    {errors.hari && <div className="text-danger">{errors.hari}</div>}
                  </div>

                  <div className="row">
                    <div className="col-sm-6">
                      <div className="form-group">
                        <label>Mulai Berlaku</label>
                        <input
                          type="date"
                          className="form-control"
                          value={data.start_date}
                          onChange={(e) => setData('start_date', e.target.value)}
                        />
                        {errors.start_date && (
                          <div className="text-danger">{errors.start_date}</div>
                        )}
                      </div>
                    </div>
                    <div className="col-sm-6">
                      <div className="form-group">
                        <label>Akhir Berlaku</label>
                        <input
                          type="date"
                          className="form-control"
                          value={data.end_date}
                          onChange={(e) => setData('end_date', e.target.value)}
                        />
                        {errors.end_date && (
                          <div className="text-danger">{errors.end_date}</div>
                        )}
                      </div>
                    </div>
                  </div>

                  <div className="row">
                    <div className="col-sm-6">
                      <div className="form-group">
                        <label>Jam Masuk</label>
                        <input
                          type="time"
                          className="form-control"
                          value={data.jam_masuk}
                          onChange={(e) => setData('jam_masuk', e.target.value)}
                        />
                        {errors.jam_masuk && (
                          <div className="text-danger">{errors.jam_masuk}</div>
                        )}
                      </div>
                    </div>
                    <div className="col-sm-6">
                      <div className="form-group">
                        <label>Jam Pulang</label>
                        <input
                          type="time"
                          className="form-control"
                          value={data.jam_pulang}
                          onChange={(e) => setData('jam_pulang', e.target.value)}
                        />
                        {errors.jam_pulang && (
                          <div className="text-danger">{errors.jam_pulang}</div>
                        )}
                      </div>
                    </div>
                  </div>
                </div>

                <div className="modal-footer">
                  <button type="button" className="btn btn-default" onClick={closeModal}>
                    Batal
                  </button>
                  <button className="btn btn-primary" disabled={processing}>
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
        show={!!deleteTarget}
        title="Konfirmasi Hapus Jadwal Sub Unit"
        message={
          <p>
            Yakin menghapus jadwal:
            <br />
            <strong>{deleteTarget?.unit_name}</strong>
            {' | '}
            <strong>{deleteTarget?.sub_unit_name}</strong>
            {' | '}
            <strong>{deleteTarget ? hariMap[deleteTarget.hari] : ''}</strong>
          </p>
        }
        onCancel={() => setDeleteTarget(null)}
        onConfirm={() => {
          if (!deleteTarget) return
          router.delete(`/jam-kerja/sub-unit/${deleteTarget.id}`, {
            preserveScroll: true,
            onSuccess: () => setDeleteTarget(null),
          })
        }}
      />
    </AdminLayout>
  )
}
