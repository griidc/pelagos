package edu.tamucc.hri.griidc.pubs;

import java.io.IOException;
import java.sql.SQLException;

import edu.tamucc.hri.griidc.utils.MiscUtils;
import edu.tamucc.hri.griidc.rdbms.RdbmsConnection;
import edu.tamucc.hri.griidc.rdbms.RdbmsPubsUtils;

abstract public class SynchronizerBase {
	
	private boolean baseInitialized = false;
	protected RdbmsConnection risDbConnection = null;
	protected RdbmsConnection griidcDbConnection = null;
	
	public SynchronizerBase() {
		// TODO Auto-generated constructor stub
	}

	
	public boolean isBaseInitialized() {
		return baseInitialized;
	}


	public RdbmsConnection getRisDbConnection() {
		return risDbConnection;
	}


	public RdbmsConnection getGriidcDbConnection() {
		return griidcDbConnection;
	}


	abstract protected void initialize();
	
	public void commonInitialize()  {
		
		String rdbmsDescription = null;
		String cName = this.getClass().getName();
		if (!baseInitialized) {
			try {
				MiscUtils.openPrimaryLogFile();
				MiscUtils.openErrorLogFile();
				MiscUtils.openDeveloperReportFile();
			} catch (IOException e) {
				System.err.println("IOException in class: " + cName + " function: commonInitialize()");
				System.err.println("exception: " + e.getMessage());
				System.exit(-1);
			}
			try {
				this.risDbConnection = RdbmsPubsUtils.getRisDbConnectionInstance();
			} catch (SQLException e) {
				System.err.println("SQLException in class: " + cName + " function: commonInitialize()");
				System.err.println("exception: " + e.getMessage());
				System.exit(-1);
			}
			
			try {
				this.griidcDbConnection = RdbmsPubsUtils
						.getGriidcDbConnectionInstance();
			} catch (SQLException e) {
				System.err.println("SQLException in class: " + cName + " function: commonInitialize()");
				System.err.println("exception: " + e.getMessage());
				System.exit(-1);
			}
			baseInitialized = true;
		}
	}
}
