import React, { useState, useRef, useEffect } from "react";

export default function RekapActions({ pdfUrl, excelUrl }) {
    const [open, setOpen] = useState(false);
    const ref = useRef();

    const toggle = () => setOpen(!open);

    useEffect(() => {
        const handler = (e) => {
            if (!ref.current?.contains(e.target)) {
                setOpen(false);
            }
        };
        document.addEventListener("click", handler);
        return () => document.removeEventListener("click", handler);
    }, []);

    return (
        <div className="btn-group" ref={ref}>

            {/* PRINT */}
            <button
                className="btn btn-success btn-sm"
                onClick={() => window.print()}
            >
                <i className="fa fa-print" /> Print
            </button>

            {/* DROPDOWN BUTTON */}
            <button
                className="btn btn-primary btn-sm dropdown-toggle"
                onClick={toggle}
            >
                <i className="fa fa-download" /> Export <span className="caret" />
            </button>

            {/* MENU */}
            {open && (
                <ul
                    className="dropdown-menu dropdown-menu-right"
                    style={{ display: "block" }}
                >
                    <li>
                        <a href={pdfUrl}>
                            <i className="fa fa-file-pdf-o" /> Export PDF
                        </a>
                    </li>

                    <li>
                        <a href={excelUrl}>
                            <i className="fa fa-file-excel-o" /> Export Excel (.xlsx)
                        </a>
                    </li>
                </ul>
            )}
        </div>
    );
}