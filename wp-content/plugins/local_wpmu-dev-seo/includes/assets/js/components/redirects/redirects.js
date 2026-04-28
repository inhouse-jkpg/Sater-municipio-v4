import React from 'react';
import { __, _n, sprintf } from '@wordpress/i18n';
import Button from '../button';
import classnames from 'classnames';
import RedirectItem from './redirect-item';
import RedirectModal from './redirect-modal';
import { pickBy } from 'lodash-es';
import update from 'immutability-helper';
import BulkUpdateRedirectsModal from './bulk-update-redirects-modal';
import Pagination from '../pagination';
import PaginationUtil from '../../utils/pagination-util';
import SUI from 'SUI';
import UrlUtil from '../../utils/url-util';
import RequestUtil from '../../utils/request-util';
import ConfirmationModal from '../confirmation-modal';
import { ImportRedirectsModal } from './import-redirects-modal';
import FileUtil from '../../utils/file-util';
import { DateTime } from 'luxon';
import { createInterpolateElement } from '@wordpress/element';
import Search from '../search';
import memoizeOne from 'memoize-one';
import Notice from '../notices/notice';
import ConfigValues from '../../es6/config-values';

const isNewFeature = ConfigValues.get('new_feature_status', 'admin') !== '3';

const homeUrl = ConfigValues.get('home_url', 'redirects').replace(/\/$/, '');

export default class Redirects extends React.Component {
	static defaultProps = {
		redirects: {},
		nonce: '',
		csvTypes: [],
	};

	constructor(props) {
		super(props);

		this.state = {
			redirects: this.props.redirects,
			keyword: '',
			bulkItems: new Set(),
			addingRedirect: false,
			editingRedirect: false,
			deletingRedirect: false,
			bulkUpdating: false,
			currentPageNumber: 1,
			requestInProgress: false,
			importInProgress: false,
			exportInProgress: false,
		};

		this.redirectsPerPage = 10;
		this.filterMemoized = memoizeOne((keyword, redirects) =>
			this.filterRedirects(keyword, redirects)
		);
	}

	componentDidMount() {
		this.maybeStartAddingRedirect();
	}

	render() {
		const {
			bulkUpdating,
			bulkItems,
			requestInProgress,
			editingRedirect,
			addingRedirect,
			deletingRedirect,
			currentPageNumber,
			importInProgress,
			exportInProgress,
			keyword,
		} = this.state;
		const redirects = this.getFilteredRedirects();
		const redirectsCount = this.objectLength(redirects);
		const redirectsExist = redirectsCount > 0;
		const page = this.getRedirectsPage();
		const pageLength = this.objectLength(page);
		const bulkCount = bulkItems.size;
		const allChecked = pageLength > 0 && bulkCount === pageLength;

		return (
			<div className="sui-box">
				<div className="sui-box-header">
					<h2 className="sui-box-title">
						{__('URL Redirection', 'wds')}
					</h2>

					<div className="sui-actions-right">
						<Button
							id="wds-import-redirects-button"
							text={__('Import', 'wds')}
							ghost={true}
							icon="sui-icon-upload-cloud"
							disabled={requestInProgress}
							onClick={() => this.startImporting()}
						/>

						<Button
							id="wds-export-redirects-button"
							text={__('Export', 'wds')}
							ghost={true}
							icon="sui-icon-cloud-migration"
							disabled={requestInProgress}
							loading={exportInProgress}
							onClick={() => this.exportRedirects()}
						/>
					</div>
				</div>

				<div className="sui-box-body">
					<p>
						{__(
							'Automatically redirect traffic from one URL to another. Use this tool if you have changed a page’s URL and wish to keep traffic flowing to the new page.',
							'wds'
						)}
					</p>

					{!!isNewFeature && (
						<Notice
							type="info"
							message={
								<>
									{createInterpolateElement(
										__(
											'<strong>New</strong>: You can now add Geolocation-based rules to redirects. This will ensure users are redirected to the most relevant content based on their locations. <a>Add a new redirect</a> to set location rules.',
											'wds'
										),
										{
											strong: <strong />,
											a: (
												<a
													href="#"
													onClick={() =>
														this.startAddingRedirect()
													}
												/>
											),
										}
									)}
									<span className="wds-new-feature-status" />
								</>
							}
						/>
					)}

					<div>
						<div className="sui-box-builder">
							<div
								className="sui-box-builder-header"
								style={{
									display: 'flex',
									justifyContent: 'space-between',
									flexWrap: 'wrap',
								}}
							>
								<Button
									text={__('Add Redirect', 'wds')}
									color="purple"
									icon="sui-icon-plus"
									onClick={() => this.startAddingRedirect()}
								/>

								<div>
									{redirectsCount > this.redirectsPerPage && (
										<Pagination
											count={redirectsCount}
											currentPage={currentPageNumber}
											perPage={this.redirectsPerPage}
											onClick={(pageNumber) =>
												this.changePage(pageNumber)
											}
										/>
									)}
								</div>
							</div>

							<div
								className={classnames('sui-box-builder-body', {
									'wds-no-redirects':
										!redirectsExist && !keyword,
								})}
							>
								<div className="wds-redirect-controls">
									<div>
										<label className="sui-checkbox">
											<input
												type="checkbox"
												checked={allChecked}
												onChange={(e) =>
													this.toggleAll(
														e.target.checked
													)
												}
											/>
											<span aria-hidden="true" />
										</label>

										<Button
											id="wds-redirects-start-bulk-update"
											text={__(
												'Bulk Update',
												'wds'
											)}
											onClick={() =>
												this.startBulkUpdate()
											}
											disabled={!bulkCount}
										/>
										{bulkUpdating && (
											<BulkUpdateRedirectsModal
												inProgress={requestInProgress}
												homeUrl={homeUrl}
												onSave={(destination, type) =>
													this.bulkUpdate(
														destination,
														type
													)
												}
												onClose={() =>
													this.stopBulkUpdate()
												}
											/>
										)}

										<Button
											id="wds-redirects-start-bulk-removing"
											text={__(
												'Remove Redirects',
												'wds'
											)}
											onClick={() =>
												this.startDeletingRedirects()
											}
											disabled={!bulkCount}
										/>
									</div>

									<Search
										placeholder={__(
											'Search Redirects',
											'wds'
										)}
										onChange={(kw) => this.handleSearch(kw)}
									/>
								</div>

								<div className="sui-builder-fields">
									{redirectsExist && (
										<div className="wds-redirect-item wds-redirect-item-columns">
											<div className="wds-redirect-item-checkbox" />

											<div className="wds-redirect-item-source">
												<small>
													<strong>
														{__(
															'From',
															'wds'
														)}
													</strong>
												</small>
											</div>

											<div className="wds-redirect-item-destination">
												<small>
													<strong>
														{__(
															'To',
															'wds'
														)}
													</strong>
												</small>
											</div>

											<div className="wds-redirect-item-options">
												<small>
													<strong>
														{__(
															'Type',
															'wds'
														)}
													</strong>
												</small>
											</div>

											<div className="wds-redirect-item-dropdown" />
										</div>
									)}

									{Object.keys(page).map((id) => {
										const item = page[id];
										return (
											<React.Fragment
												key={id + '-fragment'}
											>
												<RedirectItem
													{...item}
													key={id}
													selected={bulkItems.has(id)}
													onToggle={(selected) =>
														this.toggleItem(
															id,
															selected
														)
													}
													onEdit={() =>
														this.startEditingRedirect(
															id
														)
													}
													onDelete={() =>
														this.startDeletingRedirect(
															id
														)
													}
												/>

												{editingRedirect === id && (
													<RedirectModal
														{...item}
														inProgress={
															requestInProgress
														}
														onSave={(
															updatedRedirect
														) =>
															this.editRedirect(
																updatedRedirect
															)
														}
														onClose={() =>
															this.stopEditingRedirect()
														}
													/>
												)}
											</React.Fragment>
										);
									})}
								</div>

								{keyword && !redirectsExist && (
									<p>
										{createInterpolateElement(
											sprintf(
												// translators: %s: Keyword.
												__(
													'No results found for the keyword <strong>%s</strong>.',
													'wds'
												),
												keyword
											),
											{ strong: <strong /> }
										)}
									</p>
								)}

								<Button
									id="wds-add-redirect-dashed-button"
									dashed={true}
									icon="sui-icon-plus"
									text={__('Add Redirect', 'wds')}
									onClick={() => this.startAddingRedirect()}
								/>

								<p className="wds-no-redirects-message">
									<small>
										{__(
											'You can add as many redirects as you like. Add your first above!',
											'wds'
										)}
									</small>
								</p>
							</div>
						</div>

						{addingRedirect && (
							<RedirectModal
								options={[]}
								inProgress={requestInProgress}
								onSave={(redirectData) =>
									this.addRedirect(redirectData)
								}
								onClose={() => this.stopAddingRedirect()}
							/>
						)}

						{deletingRedirect && (
							<ConfirmationModal
								id="wds-delete-redirect-modal"
								title={__('Are you sure?', 'wds')}
								description={_n(
									'Are you sure you want to delete this redirect? This action is irreversible.',
									'Are you sure you want to delete the selected redirects? This action is irreversible.',
									deletingRedirect.length,
									'wds'
								)}
								inProgress={requestInProgress}
								onClose={() => this.stopDeletingRedirect()}
								onDelete={() => this.deleteRedirect()}
							/>
						)}
					</div>

					{importInProgress && (
						<ImportRedirectsModal
							inProgress={requestInProgress}
							onClose={() => this.stopImporting()}
							onImport={(file) =>
								this.importRedirectsFromCsv(file)
							}
							csvTypes={this.props.csvTypes}
						/>
					)}
				</div>
			</div>
		);
	}

	exportRedirects() {
		const { nonce } = this.props;

		this.setState(
			{
				exportInProgress: true,
				requestInProgress: true,
			},
			() => {
				RequestUtil.post('wds_export_csv', nonce)
					.then((data) => {
						const host = UrlUtil.getUrlHost(homeUrl);
						const date = DateTime.now().toFormat('dd-LL-y');

						FileUtil.triggerFileDownload(
							data.content,
							`smartcrawl-redirects-${host}-${date}.csv`
						);
					})
					.catch((errorMessage) => {
						this.showErrorNotice(errorMessage);
					})
					.finally(() => {
						this.setState({
							exportInProgress: false,
							requestInProgress: false,
						});
					});
			}
		);
	}

	startImporting() {
		this.setState({ importInProgress: true });
	}

	stopImporting() {
		this.setState({ importInProgress: false });
	}

	importRedirectsFromCsv(file) {
		this.setState({ requestInProgress: true }, () => {
			RequestUtil.uploadFile(
				'wds_import_redirects_from_csv',
				this.props.nonce,
				file
			)
				.then((data) => {
					const insertedCount = data.count;
					this.showSuccessNotice(
						// translators: %d: Inserted count.
						_n(
							'%d redirect inserted successfully!',
							'%d redirects inserted successfully!',
							insertedCount,
							'wds'
						)
					);
					this.setState({ redirects: data.redirects }, () => {
						this.setState({
							currentPageNumber: this.getPageCount(),
						});
					});
				})
				.catch((message) => {
					this.showErrorNotice(message);
				})
				.finally(() => {
					this.setState({
						requestInProgress: false,
						importInProgress: false,
					});
				});
		});
	}

	changePage(pageNumber) {
		this.setState({
			currentPageNumber: pageNumber,
			bulkItems: new Set(),
		});
	}

	saveRedirect(redirectData) {
		return new Promise((resolve, reject) => {
			this.setState({ requestInProgress: true });
			this.post('wds_save_redirect', redirectData)
				.then((updatedRedirect) => {
					const redirects = update(this.state.redirects, {
						[updatedRedirect.id]: { $set: { ...updatedRedirect } },
					});
					this.setState({ redirects }, resolve);
				})
				.catch((message) => {
					this.showErrorNotice(message || '');
					reject();
				})
				.finally(() => {
					this.setState({ requestInProgress: false });
				});
		});
	}

	post(action, data) {
		return RequestUtil.post(action, this.props.nonce, data);
	}

	startAddingRedirect() {
		this.setState({ addingRedirect: true });
	}

	addRedirect(redirectData) {
		const msg = this.validateRedirect(redirectData);

		if (msg) {
			this.showErrorNotice(msg);
		} else {
			this.saveRedirect(redirectData).then(() => {
				this.showSuccessNotice(
					__('The redirect has been added.', 'wds')
				);
				this.setState({
					currentPageNumber: this.getPageCount(),
				});
				this.stopAddingRedirect();
			});
		}
	}

	validateRedirect(data) {
		const source = data.source;

		if (
			Object.values(this.state.redirects).filter((redirect) => {
				return (
					(!data.id || redirect.id !== data.id) &&
					this.getFullUrl(redirect.source) === this.getFullUrl(source)
				);
			}).length > 0
		) {
			return __(
				'That URL already exists, please try again.',
				'wds'
			);
		}

		const chain = this.findChainDFS(
			this.getFullUrl(data.destination),
			this.getFullUrl(source)
		);

		if (chain.length) {
			return sprintf(
				/* translators: 1: redirect destination, 2: redirect source */
				__(
					'Redirect chain from <strong>%1$s</strong> to <strong>%2$s</strong> already exists.',
					'wds'
				),
				data.destination,
				data.source
			);
		}
	}

	findChainDFS(source, destination, visited = []) {
		const { redirects } = this.state;

		visited.push(source);

		// Finds all redirects where the source matches the current source.
		const nextRedirects = Object.values(redirects).filter(
			(redirect) => this.getFullUrl(redirect.source) === source
		);

		for (const redirect of nextRedirects) {
			if (this.getFullUrl(redirect.destination) === destination) {
				// Found the destination, return the chain
				return [source, destination];
			} else if (
				!visited.includes(this.getFullUrl(redirect.destination))
			) {
				// Recursively search for the destination in the next object
				const subChain = this.findChainDFS(
					this.getFullUrl(redirect.destination),
					destination,
					visited
				);

				if (subChain.length > 0) {
					// Add the current source to the sub-chain and return it
					subChain.unshift(source);
					return subChain;
				}
			}
		}

		// If the destination is not found, return an empty array
		return [];
	}

	getFullUrl(url) {
		if (url[0] === '/') {
			url = homeUrl + url;
		}

		return url.replace(/\/$/, '');
	}

	stopAddingRedirect() {
		this.maybeRemoveQueryParam();
		this.setState({ addingRedirect: false });
	}

	startEditingRedirect(id) {
		this.setState({ editingRedirect: id });
	}

	editRedirect(redirectData) {
		const msg = this.validateRedirect(redirectData);

		if (msg) {
			this.showErrorNotice(msg);
		} else {
			this.saveRedirect(redirectData).then(() => {
				this.stopEditingRedirect();
				this.showSuccessNotice(
					__('The redirect has been updated.', 'wds')
				);
			});
		}
	}

	stopEditingRedirect() {
		this.setState({ editingRedirect: false });
	}

	startBulkUpdate() {
		this.setState({ bulkUpdating: true });
	}

	bulkUpdate(destination, type) {
		const ids = Array.from(this.state.bulkItems);
		this.setState({ requestInProgress: true });
		this.post('wds_bulk_update_redirects', { ids, destination, type })
			.then((updatedRedirects) => {
				const spec = {};
				ids.forEach((id) => {
					spec[id] = { $set: { ...updatedRedirects[id] } };
				});
				this.setState({
					redirects: update(this.state.redirects, spec),
					bulkUpdating: false,
					requestInProgress: false,
				});
				this.showSuccessNotice(
					__('The redirects have been updated.', 'wds')
				);
			})
			.catch((message) => {
				this.showErrorNotice(message);
			});
	}

	stopBulkUpdate() {
		this.setState({ bulkUpdating: false });
	}

	startDeletingRedirect(id) {
		this.setState({ deletingRedirect: [id] });
	}

	startDeletingRedirects() {
		const ids = Array.from(this.state.bulkItems);
		this.setState({ deletingRedirect: ids });
	}

	stopDeletingRedirect() {
		this.setState({ deletingRedirect: false });
	}

	deleteRedirect() {
		const ids = this.state.deletingRedirect;
		this.setState({ requestInProgress: true });
		this.post('wds_delete_redirect', { ids })
			.then(() => {
				const redirects = update(this.state.redirects, { $unset: ids });

				const bulkItemSet = update(this.state.bulkItems, {
					$remove: ids,
				});

				this.setState({ redirects }, () => {
					this.setState({
						deletingRedirect: false,
						requestInProgress: false,
						currentPageNumber: this.newPageNumberAfterDeletion(),
						bulkItems: bulkItemSet,
					});
				});

				this.showSuccessNotice(
					_n(
						'The redirect has been removed.',
						'The redirects have been removed.',
						ids.length,
						'wds'
					)
				);
			})
			.catch((message) => {
				this.showErrorNotice(message);
			});
	}

	toggleItem(id, selected) {
		const set = new Set(this.state.bulkItems);
		if (selected) {
			set.add(id);
		} else {
			set.delete(id);
		}
		this.setState({
			bulkItems: set,
		});
	}

	toggleAll(selected) {
		let bulkItems;
		if (selected) {
			bulkItems = Object.keys(this.getRedirectsPage());
		} else {
			bulkItems = [];
		}
		this.setState({
			bulkItems: new Set(bulkItems),
		});
	}

	getPageCount() {
		return PaginationUtil.getPageCount(
			this.objectLength(this.getFilteredRedirects()),
			this.redirectsPerPage
		);
	}

	getRedirectsPage() {
		return PaginationUtil.getPage(
			this.getFilteredRedirects(),
			this.state.currentPageNumber,
			this.redirectsPerPage
		);
	}

	newPageNumberAfterDeletion() {
		const currentPageNumber = this.state.currentPageNumber;
		return currentPageNumber > this.getPageCount()
			? currentPageNumber - 1
			: currentPageNumber;
	}

	objectLength(collectionObject) {
		return Object.keys(collectionObject).length;
	}

	showNotice(message, type = 'success') {
		const icons = {
			error: 'warning-alert',
			info: 'info',
			warning: 'warning-alert',
			success: 'check-tick',
		};

		SUI.closeNotice('wds-redirect-notice');
		SUI.openNotice('wds-redirect-notice', '<p>' + message + '</p>', {
			type,
			icon: icons[type],
			dismiss: { show: false },
		});
	}

	showSuccessNotice(message) {
		this.showNotice(message, 'success');
	}

	showErrorNotice(message) {
		this.showNotice(
			message
				? message
				: __(
						'An error occurred. Please reload the page and try again!',
						'wds'
				  ),
			'error'
		);
	}

	maybeStartAddingRedirect() {
		if (UrlUtil.getQueryParam('add_redirect') === '1') {
			this.startAddingRedirect();
		}
	}

	maybeRemoveQueryParam() {
		UrlUtil.removeQueryParam('add_redirect');
	}

	handleSearch(keyword) {
		this.setState({
			keyword,
			bulkItems: new Set(),
			currentPageNumber: 1,
		});
	}

	getFilteredRedirects() {
		const { keyword, redirects } = this.state;

		return this.filterMemoized(keyword, redirects);
	}

	filterRedirects(keyword, redirects) {
		return pickBy(redirects, (redirect) => {
			return this.redirectMatchesKeyword(keyword, redirect);
		});
	}

	redirectMatchesKeyword(keyword, redirect) {
		if (!keyword) {
			return true;
		}

		let matches = false;
		['source', 'destination', 'title'].some((redirectProperty) => {
			if (!redirect || !redirect.hasOwnProperty(redirectProperty)) {
				return false;
			}

			const propertyValue = redirect[redirectProperty].toLowerCase(),
				lowerCaseKeyword = keyword.toLowerCase();

			if (propertyValue.indexOf(lowerCaseKeyword) !== -1) {
				matches = true;
				return true;
			}

			return false;
		});

		return matches;
	}
}
