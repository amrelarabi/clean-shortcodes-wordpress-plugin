import React, { useState, useEffect } from "react";
import { __ } from "@wordpress/i18n";

const App = () => {
    const [unusedShortcodes, setUnusedShortcodes] = useState([]);
    const [usedShortcodes, setUsedShortcodes] = useState([]);
    const [loading, setLoading] = useState(false);
    const [activeTab, setActiveTab] = useState("unused");

    // Fetch data from the backend
    useEffect(() => {
        fetchShortcodes();
    }, []);

    const fetchShortcodes = async () => {
        setLoading(true);
        try {
            const response = await fetch(cus_ajax_object.admin_ajax, {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: new URLSearchParams({
                    action: "cus_fetch_shortcodes",
                    _wpnonce: cus_ajax_object.cleanShortcodesNonce,
                }),
            });

            const result = await response.json();
            if (result.success) {
                setUnusedShortcodes(result.data.unused_shortcodes || []);
                setUsedShortcodes(result.data.used_shortcodes || []);
            } else {
                alert(__("Error fetching shortcodes.", "clean-unused-shortcodes"));
            }
        } catch (error) {
            alert(__("An error occurred while fetching shortcodes.", "clean-unused-shortcodes"));
        } finally {
            setLoading(false);
        }
    };

    const cleanAllShortcodes = async () => {
        if (!confirm(__("Are you sure you want to clean all unused shortcodes?", "clean-unused-shortcodes"))) {
            return;
        }

        setLoading(true);

        try {
            const response = await fetch(cus_ajax_object.admin_ajax, {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: new URLSearchParams({
                    action: "cus_clean_all_shortcode",
                    _wpnonce: cus_ajax_object.cleanShortcodesNonce,
                }),
            });

            const result = await response.json();
            if (result.success) {
                alert(__("All unused shortcodes cleaned successfully!", "clean-unused-shortcodes"));
                fetchShortcodes(); // Refresh the list after cleaning

            } else {
                alert(__("Error cleaning shortcodes.", "clean-unused-shortcodes"));
            }
        } catch (error) {
            alert(__("An error occurred while cleaning shortcodes.", "clean-unused-shortcodes"));
        } finally {
            setLoading(false);
        }
    };


    const cleanShortcode = async (shortcode) => {
        if (!confirm(`${__("Are you sure you want to remove this shortcode?", "clean-unused-shortcodes")} ${shortcode}`)) {
            return;
        }

        setLoading(true);

        try {
            const response = await fetch(ajaxurl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: new URLSearchParams({
                    action: "cus_clean_shortcode",
                    shortcode,
                    _wpnonce: cus_ajax_object.cleanShortcodesNonce,
                }),
            });

            const result = await response.json();
            if (result.success) {
                alert(__("Shortcode removed successfully!", "clean-unused-shortcodes"));
                fetchShortcodes(); // Refresh the list after cleaning
            } else {
                alert(__("Error removing shortcode.", "clean-unused-shortcodes"));
            }
        } catch (error) {
            alert(__("An error occurred while cleaning the shortcode.", "clean-unused-shortcodes"));
        } finally {
            setLoading(false);
        }
    };

    const renderTabContent = () => {
        if (loading) {
            return <p>{__("Loading...", "clean-unused-shortcodes")}</p>;
        }

        if (activeTab === "unused") {
            return (
                <>
                    <h3>{__("Unused Shortcodes", "clean-unused-shortcodes")}</h3>
                    {unusedShortcodes.length > 0 ? (
                        <>
                        <button
                            className="button button-primary clean-all"
                            onClick={cleanAllShortcodes}
                            disabled={loading}
                        >
                            {loading
                                ? __("Cleaning All...", "clean-unused-shortcodes")
                                : __("Clean All Unused Shortcodes", "clean-unused-shortcodes")}
                        </button>
                        <table className="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th>{__("Shortcode", "clean-unused-shortcodes")}</th>
                                    <th>{__("Location", "clean-unused-shortcodes")}</th>
                                    <th>{__("Action", "clean-unused-shortcodes")}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {unusedShortcodes.map((shortcode) => (
                                    <tr key={shortcode.name}>
                                        <td>{shortcode.name}</td>
                                        <td>
                                            <ul>
                                                {shortcode.locations.map((location, index) => (
                                                    <li key={index}>
                                                        {location.title} ({location.post_type})
                                                        <a href={location.edit_link} className="edit-post" target="_blank" rel="noopener noreferrer">
                                                            {__("Edit", "clean-unused-shortcodes")}
                                                        </a>
                                                        {" | "}
                                                        <a href={location.view_link} target="_blank" className="view-post" rel="noopener noreferrer">
                                                            {__("View", "clean-unused-shortcodes")}
                                                        </a>
                                                    </li>
                                                ))}
                                            </ul>
                                        </td>
                                        <td>
                                            <button
                                                className="button button-primary clean-btn"
                                                onClick={() => cleanShortcode(shortcode.name)}
                                            >
                                                {__("Clean", "clean-unused-shortcodes")}
                                            </button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>

                        </table>
                        </>
                    ) : (
                        <p>{__("No unused shortcodes found.", "clean-unused-shortcodes")}</p>
                    )}
                </>
            );
        }

        if (activeTab === "used") {
            return (
                <>
                    <h3>{__("Used Shortcodes", "clean-unused-shortcodes")}</h3>
                    {usedShortcodes.length > 0 ? (
                        <ul>
                            {usedShortcodes.map((shortcode, index) => (
                                <li key={index}>{shortcode}</li>
                            ))}
                        </ul>
                    ) : (
                        <p>{__("No used shortcodes found.", "clean-unused-shortcodes")}</p>
                    )}
                </>
            );
        }

        return null;
    };

    return (
        <div className="clean-unused-shortcodes">
            <h1>{__("Clean Unused Shortcodes", "clean-unused-shortcodes")}</h1>
            <p>
                {__(
                    "Use this tool to manage unused shortcodes in your posts, pages, and other post types.",
                    "clean-unused-shortcodes"
                )}
            </p>
            <div className="tabs">
                <button
                    className={`button ${activeTab === "unused" ? "button-primary" : ""}`}
                    onClick={() => setActiveTab("unused")}
                >
                    {__("Unused Shortcodes", "clean-unused-shortcodes")}
                </button>
                <button
                    className={`button ${activeTab === "used" ? "button-primary" : ""}`}
                    onClick={() => setActiveTab("used")}
                >
                    {__("Used Shortcodes", "clean-unused-shortcodes")}
                </button>
            </div>
            <div className="tab-content">{renderTabContent()}</div>
        </div>
    );
};

export default App;
